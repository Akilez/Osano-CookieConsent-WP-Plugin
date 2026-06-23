(function () {
  if (window.ccboCookieConsentLoaded) {
    return;
  }

  window.ccboCookieConsentLoaded = true;

  var LOCATION_CACHE_KEY = 'ccboCookieConsentLocationV1';
  var loadedDeferredScripts = {};
  var consentBypass = false;
  var EU_COUNTRY_CODES = [
    'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
    'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
    'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
  ];

  function dispatchEvent(name, detail) {
    document.dispatchEvent(
      new CustomEvent(name, {
        detail: detail || {}
      })
    );
  }

  function dispatchConsentEvent(name, instance) {
    var status = getConsentStatus(instance);
    var allowsTracking = consentAllowsTracking(status, getConsentMode(instance));

    dispatchEvent(name, {
      status: status,
      hasConsented: allowsTracking,
      allowsTracking: allowsTracking,
      instance: instance || null
    });
  }

  function getConsentMode(instance) {
    if (
      instance &&
      instance.options &&
      typeof instance.options.type === 'string'
    ) {
      return instance.options.type;
    }

    if (
      window.ccboCookieConsent &&
      typeof window.ccboCookieConsent.getConfig === 'function'
    ) {
      return window.ccboCookieConsent.getConfig().type || 'opt-in';
    }

    return 'opt-in';
  }

  function getCookieValue(name) {
    var encodedName = name + '=';
    var parts = document.cookie ? document.cookie.split(';') : [];
    var index;

    for (index = 0; index < parts.length; index += 1) {
      var cookie = parts[index].replace(/^\s+/, '');

      if (cookie.indexOf(encodedName) === 0) {
        return cookie.substring(encodedName.length);
      }
    }

    return '';
  }

  function getConsentStatus(instance) {
    if (instance && typeof instance.status === 'string') {
      return instance.status;
    }

    if (
      window.ccboCookieConsent &&
      typeof window.ccboCookieConsent.getStatus === 'function'
    ) {
      return window.ccboCookieConsent.getStatus();
    }

    return '';
  }

  function consentAllowsTracking(status, mode) {
    if (consentBypass) {
      return true;
    }

    if (status === 'deny') {
      return false;
    }

    if (mode === 'info') {
      return true;
    }

    if (mode === 'opt-out') {
      return status !== 'deny';
    }

    return status === 'allow';
  }

  function syncGa4Consent() {
    if (
      !window.ccboCookieConsent ||
      typeof window.ccboCookieConsent.getConfig !== 'function'
    ) {
      return;
    }

    var config = window.ccboCookieConsent.getConfig();

    if (
      !config.ga4 ||
      config.ga4.enabled !== true ||
      typeof window.gtag !== 'function'
    ) {
      return;
    }

    window.gtag('consent', 'update', {
      analytics_storage: window.ccboCookieConsent.allowsTracking()
        ? 'granted'
        : 'denied'
    });
  }

  function getLocationSettings(config) {
    if (!config || !config.ulcLocation || config.ulcLocation.enabled !== true) {
      return null;
    }

    return config.ulcLocation;
  }

  function readLocationCache() {
    try {
      var raw = window.localStorage.getItem(LOCATION_CACHE_KEY);
      var parsed = raw ? JSON.parse(raw) : null;

      if (!parsed || !parsed.expiresAt || Date.now() > parsed.expiresAt) {
        return null;
      }

      return parsed;
    } catch (error) {
      return null;
    }
  }

  function writeLocationCache(data, cacheHours) {
    try {
      window.localStorage.setItem(
        LOCATION_CACHE_KEY,
        JSON.stringify({
          countryCode: data.countryCode,
          inEu: data.inEu,
          expiresAt: Date.now() + (cacheHours * 60 * 60 * 1000)
        })
      );
    } catch (error) {
      // Ignore storage failures and continue without cache persistence.
    }
  }

  function detectEuStatus(response) {
    if (typeof response.in_eu === 'boolean') {
      return response.in_eu;
    }

    if (typeof response.country_code === 'string') {
      return EU_COUNTRY_CODES.indexOf(response.country_code.toUpperCase()) !== -1;
    }

    return false;
  }

  function resolveLocation(locationSettings, callback) {
    var cached = readLocationCache();

    if (cached) {
      callback(null, cached);
      return;
    }

    var request = new XMLHttpRequest();
    request.open('GET', locationSettings.endpoint, true);
    request.timeout = locationSettings.timeout;

    request.onreadystatechange = function () {
      if (request.readyState !== 4) {
        return;
      }

      if (request.status < 200 || request.status >= 300) {
        callback(new Error('lookup-http-' + request.status));
        return;
      }

      try {
        var response = JSON.parse(request.responseText);
        var locationData = {
          countryCode: response.country_code || '',
          inEu: detectEuStatus(response)
        };

        if (!locationData.countryCode) {
          callback(new Error('lookup-missing-country'));
          return;
        }

        writeLocationCache(locationData, locationSettings.cacheHours);
        callback(null, locationData);
      } catch (error) {
        callback(new Error('lookup-invalid-json'));
      }
    };

    request.ontimeout = function () {
      callback(new Error('lookup-timeout'));
    };

    request.onerror = function () {
      callback(new Error('lookup-network'));
    };

    request.send();
  }

  function initializeConsent(config) {
    if (config && typeof config === 'object' && config.ulcLocation) {
      delete config.ulcLocation;
    }

    if (
      !window.cookieconsent ||
      typeof window.cookieconsent.initialise !== 'function'
    ) {
      dispatchEvent('ccboCookieConsentUnavailable', {
        reason: 'missing-library'
      });
      return;
    }

    var originalOnInitialise = typeof config.onInitialise === 'function'
      ? config.onInitialise
      : null;
    var originalOnStatusChange = typeof config.onStatusChange === 'function'
      ? config.onStatusChange
      : null;

    config.onInitialise = function () {
      if (originalOnInitialise) {
        originalOnInitialise.apply(this, arguments);
      }

      dispatchConsentEvent('ccboCookieConsentInitialised', this);
      maybeLoadDeferredScripts();
      syncGa4Consent();
    };

    config.onStatusChange = function () {
      if (originalOnStatusChange) {
        originalOnStatusChange.apply(this, arguments);
      }

      dispatchConsentEvent('ccboCookieConsentChanged', this);
      maybeLoadDeferredScripts();
      syncGa4Consent();
    };

    window.cookieconsent.initialise(config);
  }

  function getDeferredScripts() {
    if (
      !window.ccboCookieConsent ||
      typeof window.ccboCookieConsent.getConfig !== 'function'
    ) {
      return [];
    }

    var config = window.ccboCookieConsent.getConfig();

    return Array.isArray(config.deferredScripts) ? config.deferredScripts : [];
  }

  function applyScriptAttributes(element, attributes) {
    if (!attributes || typeof attributes !== 'object') {
      return;
    }

    Object.keys(attributes).forEach(function (name) {
      var value = attributes[name];

      if (typeof value === 'boolean') {
        if (value) {
          element.setAttribute(name, name);
        }

        return;
      }

      if (value !== null && value !== undefined && value !== '') {
        element.setAttribute(name, String(value));
      }
    });
  }

  function loadDeferredScript(script) {
    if (!script || typeof script !== 'object' || !script.id) {
      return;
    }

    if (loadedDeferredScripts[script.id]) {
      return;
    }

    loadedDeferredScripts[script.id] = true;

    var element = document.createElement('script');
    applyScriptAttributes(element, script.attributes);

    if (script.src) {
      element.src = script.src;
    }

    if (script.inline) {
      element.text = script.inline;
    }

    document.head.appendChild(element);

    dispatchEvent('ccboCookieConsentDeferredScriptLoaded', {
      id: script.id,
      src: script.src || '',
      hasInline: !!script.inline
    });

    syncGa4Consent();
  }

  function maybeLoadDeferredScripts() {
    if (
      !window.ccboCookieConsent ||
      typeof window.ccboCookieConsent.allowsTracking !== 'function' ||
      !window.ccboCookieConsent.allowsTracking()
    ) {
      return;
    }

    getDeferredScripts().forEach(loadDeferredScript);
  }

  function initializeConsentApi(config) {
    window.ccboCookieConsent = {
      getConfig: function () {
        return config || {};
      },
      getStatus: function () {
        if (!config || !config.cookie || !config.cookie.name) {
          return '';
        }

        return getCookieValue(config.cookie.name);
      },
      hasAnswered: function () {
        var status = this.getStatus();
        return status === 'allow' || status === 'deny' || status === 'dismiss';
      },
      allowsTracking: function () {
        return consentAllowsTracking(this.getStatus(), config.type || 'opt-in');
      },
      loadDeferredScripts: function () {
        maybeLoadDeferredScripts();
      }
    };
  }

  function initialize() {
    var config = window.ccboCookieConsentConfig || {};
    var locationSettings = getLocationSettings(config);

    initializeConsentApi(config);

    if (!locationSettings) {
      initializeConsent(config);
      return;
    }

    resolveLocation(locationSettings, function (error, locationData) {
      if (error) {
        dispatchEvent('ccboCookieConsentLocationError', {
          reason: error.message || 'lookup-failed'
        });

        // Fail open so EU visitors are not skipped if the lookup service fails.
        initializeConsent(config);
        return;
      }

      dispatchEvent('ccboCookieConsentLocationResolved', locationData);

      if (!config.law) {
        config.law = {};
      }

      config.law.countryCode = locationData.countryCode;

      if (!locationData.inEu) {
        consentBypass = true;
        dispatchEvent('ccboCookieConsentSkipped', {
          reason: 'non-eu-visitor',
          countryCode: locationData.countryCode
        });
        maybeLoadDeferredScripts();
        return;
      }

      initializeConsent(config);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
    return;
  }

  initialize();
})();

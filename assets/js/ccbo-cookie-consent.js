(function () {
  if (window.ccboCookieConsentLoaded) {
    return;
  }

  window.ccboCookieConsentLoaded = true;

  var LOCATION_CACHE_KEY = 'ccboCookieConsentLocationV1';
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
    var status = '';
    var hasConsented = false;

    if (instance && typeof instance.status === 'string') {
      status = instance.status;
      hasConsented = status === 'allow' || status === 'dismiss';
    }

    dispatchEvent(name, {
      status: status,
      hasConsented: hasConsented,
      instance: instance || null
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

    if (typeof config.onInitialise !== 'function') {
      config.onInitialise = function () {
        dispatchConsentEvent('ccboCookieConsentInitialised', this);
      };
    }

    if (typeof config.onStatusChange !== 'function') {
      config.onStatusChange = function () {
        dispatchConsentEvent('ccboCookieConsentChanged', this);
      };
    }

    window.cookieconsent.initialise(config);
  }

  function initialize() {
    var config = window.ccboCookieConsentConfig || {};
    var locationSettings = getLocationSettings(config);

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
        dispatchEvent('ccboCookieConsentSkipped', {
          reason: 'non-eu-visitor',
          countryCode: locationData.countryCode
        });
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

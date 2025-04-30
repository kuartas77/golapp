window.addEventListener("load", function(){

    // Remove Loader
    var load_screen = document.getElementById("load_screen");
    document.body.removeChild(load_screen);

    var layoutName = 'Collapsible Menu';

    var settingsObject = {
        admin: 'Cork Admin Template',
        settings: {
            layout: {
                name: layoutName,
                toggle: true,
                darkMode: true,
                boxed: true,
                logo: {
                    darkLogo: `${window.location.origin}/img/ballon_dark.png`,
                    lightLogo: `${window.location.origin}/img/ballon.png`,
                    textDarklogo: `${window.location.origin}/img/dark.png`,
                    textLightlogo: `${window.location.origin}/img/light.png`,
                }
            }
        },
        reset: false
    }


    if (settingsObject.reset) {
        localStorage.clear()
    }

    if (localStorage.length === 0) {
        corkThemeObject = settingsObject;
    } else {

        getcorkThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getcorkThemeObject)
        ParsedObject = getParseObject;

        if (getcorkThemeObject !== null) {

            if (ParsedObject.admin === 'Cork Admin Template') {

                if (ParsedObject.settings.layout.name === layoutName) {

                    corkThemeObject = ParsedObject;
                } else {
                    corkThemeObject = settingsObject;
                }

            } else {
                if (ParsedObject.admin === undefined) {
                    corkThemeObject = settingsObject;
                }
            }

        }  else {
            corkThemeObject = settingsObject;
        }
    }

    // Get Dark Mode Information i.e darkMode: true or false

    if (corkThemeObject.settings.layout.darkMode) {
        localStorage.setItem("theme", JSON.stringify(corkThemeObject));
        getcorkThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getcorkThemeObject)

        if (getParseObject.settings.layout.darkMode) {
            document.body.classList.add('dark');
            if (document.querySelector('.navbar-logo')) {
                document.querySelector('.navbar-logo').setAttribute('src', getParseObject.settings.layout.logo.darkLogo)
            }
            if(document.querySelector('.logo-text')){
                document.querySelector('.logo-text').setAttribute('src', getParseObject.settings.layout.logo.textDarklogo)
            }
            if(document.querySelector('.topbar-logo')){
                document.querySelector('.topbar-logo').setAttribute('src', getParseObject.settings.layout.logo.darkLogo)
            }
            if(document.querySelector('.topbar-text')){
                document.querySelector('.topbar-text').setAttribute('src', getParseObject.settings.layout.logo.textDarklogo)
            }
        }
    } else {
        localStorage.setItem("theme", JSON.stringify(corkThemeObject));
        getcorkThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getcorkThemeObject)

        if (!getParseObject.settings.layout.darkMode) {
            document.body.classList.remove('dark');
            if (document.querySelector('.navbar-logo')) {
                document.querySelector('.navbar-logo').setAttribute('src', getParseObject.settings.layout.logo.lightLogo)
            }
            if(document.querySelector('.logo-text')){
                document.querySelector('.logo-text').setAttribute('src', getParseObject.settings.layout.logo.textLightlogo)
            }
            if(document.querySelector('.topbar-logo')){
                document.querySelector('.topbar-logo').setAttribute('src', getParseObject.settings.layout.logo.lightLogo)
            }
            if(document.querySelector('.topbar-text')){
                document.querySelector('.topbar-text').setAttribute('src', getParseObject.settings.layout.logo.textLightlogo)
            }

        }
    }

    // Get Layout Information i.e boxed: true or false

    if (corkThemeObject.settings.layout.boxed) {

        localStorage.setItem("theme", JSON.stringify(corkThemeObject));
        getcorkThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getcorkThemeObject)

        if (getParseObject.settings.layout.boxed) {

            if (document.body.getAttribute('layout') !== 'full-width') {
                document.body.classList.add('layout-boxed');
                if (document.querySelector('.header-container')) {
                    // document.querySelector('.header-container').classList.add('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.add('container-xxl');
                }
            } else {
                document.body.classList.remove('layout-boxed');
                if (document.querySelector('.header-container')) {
                    document.querySelector('.header-container').classList.remove('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.remove('container-xxl');
                }
            }

        }

    } else {

        localStorage.setItem("theme", JSON.stringify(corkThemeObject));
        getcorkThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getcorkThemeObject)

        if (!getParseObject.settings.layout.boxed) {

            if (document.body.getAttribute('layout') !== 'boxed') {
                document.body.classList.remove('layout-boxed');
                if (document.querySelector('.header-container')) {
                    document.querySelector('.header-container').classList.remove('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.remove('container-xxl');
                }
            } else {
                document.body.classList.add('layout-boxed');
                if (document.querySelector('.header-container')) {
                    // document.querySelector('.header-container').classList.add('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.add('container-xxl');
                }
            }
        }
    }





});

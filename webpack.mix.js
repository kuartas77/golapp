const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
//  mix.js('resources/js/app.js', 'public/js')
//  .postCss('resources/css/app.css', 'public/css', [
//      //
//  ]);

 mix.disableNotifications();
 mix.styles([
     'public/materialpro/prueba/bootstrap.min.css',
     'public/materialpro/assets/plugins/select2/dist/css/select2.min.css',
     'public/materialpro/assets/plugins/daterangepicker/daterangepicker.css',
     'public/materialpro/prueba/datatables.min.css',
     'public/materialpro/css/spinners.css',
     'public/materialpro/css/animate.css',
     'public/materialpro/prueba/style.css',
     'public/materialpro/css/icons/font-awesome/css/fontawesome-all.css',
     'public/materialpro/css/icons/simple-line-icons/css/simple-line-icons.css',
     'public/materialpro/css/icons/weather-icons/css/weather-icons.min.css',
     'public/materialpro/css/icons/linea-icons/linea.css',
     'public/materialpro/css/icons/themify-icons/themify-icons.css',
     'public/materialpro/css/icons/flag-icon-css/flag-icon.min.css',
     'public/materialpro/css/icons/material-design-iconic-font/css/materialdesignicons.min.css',
     'public/materialpro/prueba/colors/default-dark.css',
     'public/materialpro/assets/plugins/multiselect/css/multi-select.css',
     'public/materialpro/dragula.css',
     "public/materialpro/assets/plugins/wizard/steps.css",
     "public/materialpro/assets/plugins/timepicker/bootstrap-timepicker.min.css",
     "public/materialpro/assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css",
     "public/materialpro/css/icons/themify-icons/themify-icons.css",
     "public/materialpro/assets/plugins/dragula/dragula.min.css",
     "public/materialpro/assets/plugins/chartist-js/dist/chartist.min.css",
     "public/materialpro/assets/plugins/chartist-js/dist/chartist-init.css",
     "public/materialpro/assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css",
     "public/materialpro/assets/plugins/css-chart/css-chart.css",
 ], 'public/css/all.css').version().sourceMaps();
 
 mix.scripts([
     'public/materialpro/assets/plugins/jquery/jquery.min.js',
     "public/materialpro/bootstrap-filestyle.min.js",
     'public/materialpro/assets/plugins/moment/moment.js',
     'public/materialpro/assets/plugins/moment/moment-with-locales.min.js',
     'public/materialpro/assets/plugins/popper/popper.min.js',
     'public/materialpro/assets/plugins/bootstrap/js/bootstrap.min.js',
     'public/materialpro/js/jquery.slimscroll.js',
     'public/materialpro/js/waves.js',
     'public/materialpro/js/sidebarmenu.js',
     'public/materialpro/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js',
     'public/materialpro/assets/plugins/sparkline/jquery.sparkline.min.js',
     'public/materialpro/js/custom.min.js',
     'public/materialpro/assets/plugins/datatables/datatables.min.js',
     'public/materialpro/assets/plugins/daterangepicker/daterangepicker.js',
     'public/materialpro/assets/plugins/select2/dist/js/select2.full.min.js',
     'public/materialpro/assets/plugins/select2/dist/js/i18n/es.js',
     'public/materialpro/jquery.validate.min.js',
     'public/materialpro/messages_es.min.js',
     'public/materialpro/additional-methods.min.js',
     'public/materialpro/bootstrap-filestyle.min.js',
     'public/materialpro/assets/plugins/multiselect/js/jquery.multi-select.js',
     'public/materialpro/prueba/jquery.quicksearch.js',
     'public/materialpro/dragula.min.js',
     "public/materialpro/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js",
     "public/materialpro/bootstrap3-typeahead.min.js",
     "public/materialpro/assets/plugins/timepicker/bootstrap-timepicker.min.js",
     "public/materialpro/assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js",
     "public/materialpro/assets/plugins/dragula/dragula.min.js",
     "public/materialpro/assets/plugins/chartist-js/dist/chartist.min.js",
     "public/materialpro/assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js",
     "public/materialpro/assets/plugins/sparkline/jquery.sparkline.min.js",
     "public/materialpro/js/jquery.form.js",
     'public/materialpro/assets/plugins/wizard/jquery.steps.min.js',
 
 ], 'public/js/all.js').version().sourceMaps();
 
 mix.styles('public/css/default.css', 'public/css/default-mix.css').version();
 mix.scripts('public/js/config.js','public/js/config-mix.js').version();
 
 mix.copy('resources/js/app/adminInscriptionGCompetition.js','public/js/adminInscriptionGCompetition.js').version();
 mix.copy('resources/js/app/adminInscriptionGTraining.js','public/js/adminInscriptionGTraining.js').version();
 mix.copy('resources/js/app/assist.js','public/js/assist.js').version();
 mix.copy('resources/js/app/day_schedules.js','public/js/day_schedules.js').version();
 mix.copy('resources/js/app/inscriptions.js','public/js/inscriptions.js').version();
 mix.copy('resources/js/app/inscriptions_form.js','public/js/inscriptions_form.js').version();
 mix.copy('resources/js/app/matches_form.js','public/js/matches_form.js').version();
 mix.copy('resources/js/app/matches_functions.js','public/js/matches_functions.js').version();
 mix.copy('resources/js/app/payments.js','public/js/payments.js').version();
 mix.copy('resources/js/app/trainingGroupIndex.js','public/js/trainingGroupIndex.js').version();
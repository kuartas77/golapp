const mix = require('laravel-mix');
const path = require('path');
mix.alias({
    '@': path.join(__dirname, 'resources/js')
});
mix.js('resources/js/app.js', 'public/js').vue()
//  .postCss('resources/css/app.css', 'public/css', [
//      //
//  ]);

mix.disableNotifications();

//  mix.styles('public/css/default.css', 'public/css/default-mix.css').version();
//  mix.scripts('public/js/config.js','public/js/config-mix.js').version();
mix.copy('resources/js/app/css/all.css', 'public/css/all.css').version();
mix.copy('resources/js/app/js/all.js','public/js/all.js').version();

mix.copy('resources/js/app/js/adminInscriptionGCompetition.js','public/js/adminInscriptionGCompetition.js').version();
mix.copy('resources/js/app/js/adminInscriptionGTraining.js','public/js/adminInscriptionGTraining.js').version();
mix.copy('resources/js/app/js/assist.js','public/js/assist.js').version();
mix.copy('resources/js/app/js/day_schedules.js','public/js/day_schedules.js').version();
mix.copy('resources/js/app/js/inscriptions.js','public/js/inscriptions.js').version();
mix.copy('resources/js/app/js/inscriptions_form.js','public/js/inscriptions_form.js').version();
mix.copy('resources/js/app/js/matches_form.js','public/js/matches_form.js').version();
mix.copy('resources/js/app/js/matches_functions.js','public/js/matches_functions.js').version();
mix.copy('resources/js/app/js/payments.js','public/js/payments.js').version();
mix.copy('resources/js/app/js/trainingGroupIndex.js','public/js/trainingGroupIndex.js').version();
//  mix.copy('resources/js/app/formPlayer.js','public/js/formPlayer.js').version();
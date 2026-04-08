import Wizard from './Wizard.vue'
import Step from './Step.vue'
// Optional styles (not auto-imported). Users can import 'vue-wizard-steps/dist/style.css' if they want.
import './style.scss'

const plugin = {
  install(app) {
    app.component('Wizard', Wizard)
    app.component('Step', Step)
  }
}

export default plugin
export { Wizard, Step }

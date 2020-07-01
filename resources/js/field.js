App.booting((Vue, router) => {
    Vue.component('form-models-field', require('./components/FormModelsField'));
    Vue.component('models-field-add-model', require('./components/AddModel'))
})

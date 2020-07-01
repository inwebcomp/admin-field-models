<template>
    <form @submit.prevent="save">
        <div class="flex flex-wrap form--flex">
            <component
                    :key="$i"
                    v-for="(field, $i) in fields"
                    :is="resolveComponentName(field)"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :field="field"
                    v-model="field.value"
                    :errors="errors"
            />
        </div>

        <app-button submit type="add">{{ __('Добавить') }}</app-button>
    </form>
</template>

<script>
    import {Errors} from 'form-backend-validation'

    import HandlesForm from "./HandlesForm"

    const root = 'fields/models'

    export default {
        name: "AddProduct",

        mixins: [HandlesForm],

        props: {
            resourceName: {},
            resourceId: {},
            relatedResource: {},
        },

        methods: {
            resolveComponentName(field) {
                return field.prefixComponent ? 'form-' + field.component : field.component
            },

            fetch() {
                App.api.request({
                    url: root + '/' + this.resourceName + '/' + this.resourceId + '/' + this.relatedResource + '/create',
                }).then(data => {
                    this.fields = data
                })

                this.updateLastRetrievedAtTimestamp()
            },

            save() {
                if (this.loading)
                    return

                this.loading = true

                App.api.action({
                    method: 'POST',
                    url: root + '/' + this.resourceName + '/' + this.resourceId + '/' + this.relatedResource + '/store',
                    data: this.formData('POST')
                }).then(({resource}) => {
                    this.loading = false

                    App.$emit('listModelAdded', resource)

                    this.$toasted.success(this.__('Объект добавлен в список'))

                    this.errors = new Errors()
                }).catch(({data, status}) => {
                    this.loading = false

                    if (status == 422) {
                        this.errors = new Errors(data.errors)
                    }
                })
            },

            formData() {
                return _.tap(new FormData(), formData => {
                    _(this.fields).each(field => {
                        if (field.fill)
                            field.fill(formData)
                    })

                    formData.append('_method', 'POST')
                    formData.append('_retrieved_at', this.lastRetrievedAt)
                })
            },
        },
    }
</script>
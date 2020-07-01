import {Errors, Form} from 'form-backend-validation'

export default {
    props: {
        resourceName: {},
        resourceId: {},
    },

    data() {
        return {
            fields: {},
            errors: new Errors(),
            loading: false,
            lastRetrievedAt: null,
        }
    },

    created() {
        this.fetch()
    },

    methods: {
        formData(method) {
            return _.tap(new FormData(), formData => {
                _(this.fields).each(field => {
                    if (field.fill)
                        field.fill(formData)
                })

                formData.append('_method', method)
                formData.append('_retrieved_at', this.lastRetrievedAt)
            })
        },

        /**
         * Update the last retrieved at timestamp to the current UNIX timestamp.
         */
        updateLastRetrievedAtTimestamp() {
            this.lastRetrievedAt = Math.floor(new Date().getTime() / 1000)
        },
    },
}

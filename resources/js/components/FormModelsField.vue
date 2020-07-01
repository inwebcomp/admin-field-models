<template>
    <div class="models-field w-full">
        <editable-list add
                       @add="add"
                       :headers="headers"
                       v-model="models"
                       sortable
                       @sort="savePositions">
            <template slot-scope="{ item }">
                <table-sort-handle class="border-b border-grey-light uppercase text-sm text-grey-dark"/>

                <td class="py-4 w-24 pl-4 pr-2 border-b border-grey-light uppercase text-sm text-grey-dark">
                    <img v-if="item.image" class="m-auto block" :src="item.image"/>
                </td>

                <td class="py-4 px-2 border-b border-grey-light">
                    <router-link :to="item.url" class="data-table__value__link">{{ item.title }}</router-link>
                </td>
                <td class="py-4 px-2 w-1 border-b border-grey-light text-center cursor-pointer hover:text-danger"
                    @click="destroy(item.id)">
                    <i class="far fa-trash-alt text-black-50"></i>
                </td>
            </template>
        </editable-list>
    </div>
</template>

<script>
    const root = 'fields/models'

    export default {
        name: "models-field",

        props: {
            resourceName: {},
            resourceId: {},
            field: {},
        },

        data() {
            return {
                headers: [
                    '',
                    '',
                    this.__('Объект'),
                    ''
                ],
                models: []
            }
        },

        created() {
            this.fetch()

            App.$on('modelCreated', () => {
                this.fetch()
            })

            App.$on('modelUpdated', () => {
                this.fetch()
            })

            App.$on('modelDestroyed', () => {
                this.fetch()
            })

            App.$on('listModelAdded', () => {
                this.fetch()
            })
        },

        methods: {
            fetch() {
                App.api.request({
                    url: root + '/' + this.resourceName + '/' + this.resourceId + '/' + this.field.relatedResource,
                }).then(({data}) => {
                    this.models = data
                })
            },

            onlyStars(value) {
                return value.match(/^\*+$/)
            },

            add() {
                this.$showPopup('models-field-add-model', {
                    resourceName: this.resourceName,
                    resourceId: this.resourceId,
                    relatedResource: this.field.relatedResource,
                })
            },

            change(item) {
                App.api.request({
                    method: 'PUT',
                    url: root + '/' + this.resourceId + '/update',
                    data: {
                        id: item.id,
                    }
                })
            },

            destroy(id) {
                App.api.request({
                    method: 'DELETE',
                    url: root + '/' + this.resourceName + '/' + this.resourceId + '/' + this.field.relatedResource + '/' + id,
                }).then(() => {
                    this.models = this.models.filter(item => item.id != id)

                    this.$toasted.success(this.__('Объект удалён из списка'))
                })
            },

            savePositions() {
                App.api.request({
                    method: 'PUT',
                    url: root + '/' + this.resourceName + '/' + this.resourceId + '/' + this.field.relatedResource + '/positions',
                    data: {
                        items: this.models.map((param) => param.id)
                    }
                }).then(() => {
                    App.$emit('modelsPositionsUpdated')

                    this.$toasted.show(
                        this.__('Порядок изменён!'),
                        {type: 'success'}
                    )
                })
            },
        },
    }
</script>
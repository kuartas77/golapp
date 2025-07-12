<template>
    <div class="ms-container">
        <div class="ms-selectable">
            <ul class="ms-list">
                <li v-for="option in availableOptions" :key="option.id" class="ms-elem-selectable"
                    @click="addSelection(option)">
                    {{ option.name }}
                </li>
            </ul>
            <template v-if="buttons">
                <br>
                <div class="text-center">
                    <button type="button" class="btn waves-effect waves-light btn-rounded btn-info"
                        @click="addAll" :disabled="disabledAddAll">Agregar Todos</button>
                </div>
            </template>
        </div>

        <div class="ms-selection">
            <ul class="ms-list">
                <li v-for="option in selectedOptions" :key="option.id" class="ms-elem-selection"
                    @click="removeSelection(option)">
                    {{ option.name }}
                </li>
            </ul>
            <template v-if="buttons">
                <br>
                <div class="text-center">
                    <button type="button" class="btn waves-effect waves-light btn-rounded btn-info"
                        @click="removeAll" :disabled="disabledRemoveall">Quitar Todos</button>
                </div>
            </template>
        </div>
    </div>
</template>


<!-- <MultiSelect :buttons="true" :options="[
                { id: 10, name: 'Option A' },
                { id: 11, name: 'Option B' },
                { id: 12, name: 'Option C' },
                { id: 13, name: 'Option D' },
                { id: 14, name: 'Option E' },
                { id: 15, name: 'Option F' }
                ]" :preselected="[
                    { id: 14, name: 'Option E' }, { id: 15, name: 'Option F' }
                    ]" v-model="multiSelect"/> -->


<script>
export default {
    name: 'multiSelect',
    props: {
        buttons: {
            type: Boolean,
            default: false
        },
        options: {
            type: Array,
            required: true
        },
        preselected: {
            type: Array,
            default: []
        }
    },
    emits: ['update:selected'],
    data() {
        return {
            availableOptions: [],
            selectedOptions: []
        };
    },
    computed: {
        disabledAddAll: ({availableOptions}) => availableOptions.length == 0,
        disabledRemoveall: ({selectedOptions}) => selectedOptions.length == 0
    },
    watch: {
        'availableOptions'() {this.selectedOptions.sort((a,b) => a.id - b.id)},
        'selectedOptions'() {this.availableOptions.sort((a,b) => a.id - b.id)}
    },
    methods: {
        addSelection(item) {
            this.selectedOptions.push(item);
            this.availableOptions = this.availableOptions.filter(option => option.id !== item.id)
            this.availableOptions.sort((a,b) => a.id - b.id)
            this.$emit('update:selected', this.selectedOptions)
        },
        removeSelection(item) {
            this.availableOptions.push(item);
            this.selectedOptions = this.selectedOptions.filter(option => option.id !== item.id)
            this.$emit('update:selected', this.selectedOptions)
        },
        addAll() {
            this.selectedOptions = this.options
            this.availableOptions = []
            this.$emit('update:selected', this.selectedOptions)
        },
        removeAll() {
            this.availableOptions = this.options
            this.selectedOptions = []
            this.$emit('update:selected', this.selectedOptions)
        },
        checkLoadPreselected() {
            if (this.preselected.length > 0) {
                this.selectedOptions = this.preselected
                this.availableOptions = this.options.filter(option => !this.preselected.find(selected => selected.id === option.id))
            }
        }
    },
    mounted() {
        this.availableOptions = this.options
        this.checkLoadPreselected()
        this.$emit('update:modelValue', this.selectedOptions)
    }
};
</script>

<style scoped>
.ms-container {
    background: transparent url('../img/switch.png') no-repeat 50% 50%;
    width: 370px;
}

.ms-container:after {
    content: ".";
    display: block;
    height: 0;
    line-height: 0;
    font-size: 0;
    clear: both;
    min-height: 0;
    visibility: hidden;
}

.ms-container .ms-selectable,
.ms-container .ms-selection {
    background: #fff;
    color: #555555;
    float: left;
    width: 45%;
}

.ms-container .ms-selection {
    float: right;
}

.ms-container .ms-list {
    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    -webkit-transition: border linear 0.2s, box-shadow linear 0.2s;
    -moz-transition: border linear 0.2s, box-shadow linear 0.2s;
    -ms-transition: border linear 0.2s, box-shadow linear 0.2s;
    -o-transition: border linear 0.2s, box-shadow linear 0.2s;
    transition: border linear 0.2s, box-shadow linear 0.2s;
    border: 1px solid #ccc;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
    position: relative;
    height: 200px;
    padding: 0;
    overflow-y: auto;
}

.ms-container .ms-list.ms-focus {
    border-color: rgba(82, 168, 236, 0.8);
    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(82, 168, 236, 0.6);
    -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(82, 168, 236, 0.6);
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(82, 168, 236, 0.6);
    outline: 0;
    outline: thin dotted \9;
}

.ms-container ul {
    margin: 0;
    list-style-type: none;
    padding: 0;
}

.ms-container .ms-optgroup-container {
    width: 100%;
}

.ms-container .ms-optgroup-label {
    margin: 0;
    padding: 5px 0px 0px 5px;
    cursor: pointer;
    color: #999;
}

.ms-container .ms-selectable li.ms-elem-selectable,
.ms-container .ms-selection li.ms-elem-selection {
    border-bottom: 1px #eee solid;
    padding: 2px 10px;
    color: #555;
    font-size: 14px;
}

.ms-container .ms-selectable li.ms-hover,
.ms-container .ms-selection li.ms-hover {
    cursor: pointer;
    color: #fff;
    text-decoration: none;
    background-color: #08c;
}

.ms-container .ms-selectable li.disabled,
.ms-container .ms-selection li.disabled {
    background-color: #eee;
    color: #aaa;
    cursor: text;
}
</style>
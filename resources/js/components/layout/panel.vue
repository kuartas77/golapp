<template>
    <div class="layout-px-spacing ">
        <div class="row layout-top-spacing">
            <div class="col-xl-2 col-lg-2 col-sm-2" v-if="shouldShowLateral()"></div>
            <div class="layout-spacing" :class="!shouldShowLateral() ? 'col-xl-12 col-lg-12 col-sm-12': 'col-xl-8 col-lg-8 col-sm-8'">
                <div class="panel br-6 p-2">
                    <div class="panel-heading" v-if="shouldShowHeader()">
                        <slot name="head"></slot>
                    </div>

                    <div class="panel-body" v-if="shouldShowBody()">
                        <slot name="body"></slot>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-sm-2" v-if="shouldShowLateral()"></div>
        </div>
    </div>
</template>
<script setup>
import { useSlots } from 'vue'

const slots = useSlots()
const shouldShowHeader = () => !!slots.header
const shouldShowBody = () => !!slots.body
const shouldShowLateral = () => !!slots.lateral



</script>
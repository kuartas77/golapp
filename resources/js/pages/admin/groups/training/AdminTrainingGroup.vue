<template>
    <panel>
        <template header>
            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Grupos de entrenamiento</h4>
                </div>
            </div>
        </template>
        <template #body>

            <div class="parent ex-4">
                <div class="row">
                    <div class="col-md-6">
                        <draggable id="left-rm-spill" class="drag-drop" group="news_feed" ghost-class="gu-transit"
                            drag-class="el-drag-ex-4" :animation="200" :list="start">

                            <div class="card post text-post" v-for="element in start" :key="element.name">
                                <div class="card-body">
                                    <div class="media d-block d-sm-flex text-sm-start text-center">
                                        <div class="media-aside align-self-start">
                                            <img alt="avatar" src="/assets/images/delete-user-15.jpeg"
                                                class="img-fluid" />
                                        </div>
                                        <div class="media-body">
                                            <h5 class=""><span class="usr-commented">{{ element.name }}</span> commented
                                                on Irene Collins's <span class="comment-topic">Status</span></h5>
                                            <p class="meta-time">{{ element.id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </draggable>
                    </div>

                    <div class="col-md-6">
                        <draggable id="right-rm-spill" class="drag-drop" group="news_feed" ghost-class="gu-transit"
                            drag-class="el-drag-ex-4" :animation="200" :list="end">

                            <div class="card post text-post" v-for="element in end" :key="element.name">
                                <div class="card-body">
                                    <div class="media d-block d-sm-flex text-sm-start text-center">
                                        <div class="media-aside align-self-start">
                                            <img alt="avatar" src="/assets/images/delete-user-15.jpeg"
                                                class="img-fluid" />
                                        </div>
                                        <div class="media-body">
                                            <h5 class=""><span class="usr-commented">{{ element.name }}</span> commented
                                                on Irene Collins's <span class="comment-topic">Status</span></h5>
                                            <p class="meta-time">{{ element.id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </draggable>
                    </div>
                </div>
            </div>


        </template>
    </panel>
</template>
<script setup>
import "@/assets/sass/drag-drop/drag-drop.css";
import { ref } from "vue";
import { VueDraggableNext as draggable } from "vue-draggable-next";


const start = ref([
    { name: 'John', id: 1 },
    { name: 'Joao', id: 2 },
    { name: 'Jean', id: 3 },
    { name: 'Gerard', id: 4 },
]);

const end = ref([])



</script>
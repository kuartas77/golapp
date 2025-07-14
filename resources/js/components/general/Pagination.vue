<template>
<nav aria-label="navigation">
    <ul class="pagination justify-content-center">
        <li v-if="pagination.current_page > 1" class="page-item">
            <a class="page-link"
                href="#"
                @click.prevent="change(pagination.current_page -1)"
            >
                &lt; Anterior
            </a>
        </li>

        <li v-for="page in pages" :key="page" :class="[page == pagination.current_page ? 'active':'', 'page-item']" >
            <a class="page-link" 
               href="#"
               @click.stop="change(page)"
            >
                {{ page }}
            </a>
        </li>
       
        <li v-if="pagination.current_page < pagination.last_page" class="page-item">
            <a class="page-link" 
               href="#"
               @click.prevent="change(pagination.current_page + 1)"
            >
                Siguiente &gt;
            </a>
        </li>
    </ul>
</nav>
</template>

<script>
    export default {
        props: {
            pagination: {
                type: Object,
                required: true
            },
            offset: {
                type: Number,
                default: 4
            }
        },
        computed: {
            pages() {
                if (!this.pagination.to) {
                    return null;
                }
                let from = this.pagination.current_page - this.offset;
                if (from < 1) {
                    from = 1;
                }
                let to = from + (this.offset * 2);
                if (to >= this.pagination.last_page) {
                    to = this.pagination.last_page;
                }
                let pages = [];
                for (let page = from; page <= to; page++) {
                    pages.push(page);
                }
                return pages;
            },
        },
        methods: {
            change: function(page) {
                this.pagination.current_page = page;
                this.$emit('paginate');
            }
        }
    }
</script>
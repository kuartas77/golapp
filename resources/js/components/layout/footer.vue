<template>
    <!-- BEGIN FOOTER -->
    <div class="footer-wrapper">
        <div class="footer-section f-section-1">
            <p class="">Copyright © {{ dayjs().format('YYYY') }} <a target="_blank" href="https://golapp.com.co/">Golapp</a>, Todos los derechos reservados.</p>
        </div>
        <div class="footer-section f-section-2">

        </div>
    </div>
    <!-- END FOOTER -->
</template>
<script setup>
import dayjs from "@/utils/dayjs";
</script>
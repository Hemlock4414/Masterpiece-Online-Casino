<script setup>

import {defineProps} from 'vue';

import { authClient } from '@/store/AuthStore';

import router from "@/router";


const props = defineProps ({
    post_id: null
})

const postUpdate = async (id) => {
    try {
        const res = await authClient.put(`/api/posts/${id}`)

        if (res.status == 204) router.push({name: "dashboard"})

    } catch (e) {
        console.log(e);
    }
}
</script>

<template>
    <button type="submit" class="submit-btn" @click="postUpdate(props.post_id)">
        <slot></slot>
    </button>
</template>

<style scoped>
.submit-btn {
    width: fit-content;
    padding: 10px 15px;
    background-color: #1D9BF0;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 400;
    line-height: 18px;
    cursor: pointer;
    text-align: center;
}
  
.submit-btn:hover {
    background-color: #0056b3;
}
</style>
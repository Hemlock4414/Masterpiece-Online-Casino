<script setup>

const props = defineProps({
  date: String,
  title: String,
  text: String
});

</script>

<template>

    <div class="form-wrapper">

        <div class="form-group">
            <div class="date">{{ props.date }}</div>
            <div class="title">{{ props.title }}</div>
            <div class="text">{{ props.text }}</div>
            <slot></slot>
        </div>    
    </div>

</template>

<style scoped>

.form-wrapper  {
    width: 768px;
    height: fit-content;
    background-color: #FFFFFF;
    border-radius: 8px;
    border: solid 1px #F1F1F1;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Leichter Schatten */
    margin-bottom: 8px;
}

@media (max-width: 768px) {
  .form-wrapper {
    max-width: 768px;
    width: 100%;
}
}

.form-group {
  padding: 17px 21px 27px 37px;  
  min-height: 100px;
  width: 100%;
}

.date {
    font-size: 14px;
    font-weight: 400;
    line-height: 16.94px;
    color: #888888;
    display: flex;
    justify-content: end;
}

.title {
    font-size: 18px;
    font-weight: 700;
    line-height: 21.78px;
    color: #222222;
    margin-bottom: 10px;
}

.text {
    font-size: 18px;
    font-weight: 400;
    line-height: 21.78px;
    color: #444444;
    margin-bottom: 24px;
}

.btn {
    background-color: #222222;
    border-radius: 8px;
    padding: 10px 15px;
    color: #FFFFFF;
    font-size: 14px;
    line-height: 18px;
    font-weight: 400;
}

</style>
<template>
  <div class="searchWrap" :style="{borderColor: borderColor}">
    <slot>

    </slot>
  </div>
</template>

<script>
export default {
  props: {
    borderColor: {
      type: String,
      default: 'lightblue'
    }
  }
}
</script>

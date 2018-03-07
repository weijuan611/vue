<template>
  <div class="title">
    <span>{{title}}</span>
    <span class="day">
      {{date !== null ? ( date[0] === date[1] ? '(' + date[0] + ')': '(' + date[0] + ' 至 ' + date[1] + ')') : '' }}
      {{date2 !== null ? ( date2[0] === date2[1] ? '  - 对比 -  (' + date2[0] + ')' : '  - 对比 -  (' + date2[0] + ' 至 ' + date2[1] + ')' ) : '' }}
    </span>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: ''
    },
    date: {
      type: [Object, String, Array],
      default: null
    },
    date2: {
      type: [String, Array, Object],
      default: null
    }
  }
}
</script>

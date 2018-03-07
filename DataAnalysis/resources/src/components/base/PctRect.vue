<template>
  <div class="percent-rect">
    <div class="bg" :style="objStyle">
      <span>{{url}}</span>
    </div>
    <span>{{number}}</span>
  </div>
</template>

<script>
export default {
  props: {
    width: {
      type: Number,
      default: 100
    },
    color: {
      type: String,
      default: 'orange'
    },
    url: {
      type: String,
      default: ''
    },
    number: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      objStyle: {
        backgroundColor: this.color,
        width: this.width * .04 + 'px',
        maxWidth: '90%'
      }
    }
  },
  watch: {
    width () {
      this.objStyle = {
        backgroundColor: this.color,
        width: this.width * .04 + 'px',
        maxWidth: '90%'
      }
    }
  }
}
</script>

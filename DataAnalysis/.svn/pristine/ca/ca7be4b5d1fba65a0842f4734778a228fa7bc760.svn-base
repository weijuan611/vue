<template>
  <div class="mark-block">
    <div class="bg" :style="objStyle"></div>
    <span>
      <slot>
      </slot>
    </span>
  </div>
</template>

<script>
export default {
  props: {
    number: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      objStyle: {
        backgroundColor: this.colorFunc(this.number),
        width: this.number * 1 + 'px'
      }
    }
  },
  watch: {
    number () {
      this.objStyle = {
        backgroundColor: this.colorFunc(this.number),
        width: this.number + 'px'
      }
    }
  },
  methods: {
    colorFunc (number) {
      if (number >= 0 && number < 50) {
        return '#dd6161'
      } else if (number >= 50 && number < 70) {
        return '#e6a23c'
      } else {
        return '#4db3ff'
      }

    }
  }
}
</script>

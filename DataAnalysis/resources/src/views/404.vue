<template>
  <div class="error404">
    <!-- <img src="./../../assets/img/404.png" /> -->
    <span>正在为您跳转到首页...... &nbsp;&nbsp;{{time}}s&nbsp;&nbsp;</span>
  </div>
</template>

<script>
export default {
  data () {
    return {
      time: 5
    }
  },
  mounted () {
    this.countDown()
  },
  updated () {
    if (this.time > 0) {
      this.countDown()
    } else {
      this.$router.push('/index.html')
    }
  },
  methods: {
    countDown () {
      let t
      let that = this
      clearTimeout(t)
      t = setTimeout(function () {
        that.time = that.time - 1
      }, 1000)
    }
  }
}
</script>

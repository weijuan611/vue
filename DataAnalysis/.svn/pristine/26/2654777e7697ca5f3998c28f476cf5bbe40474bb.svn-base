<template>
  <el-col :span="Number(width)">
      <div class="bg">
          <el-form-item :label="label" :label-width="labelWidth">
            <slot>
            </slot>
          </el-form-item>
      </div>
  </el-col>
</template>

<script>
  export default {
    props: {
      label: '',
      width: String,
      withBg: true
    },
    data () {
      return {
        labelWidth: '100px'
      }
    },
    computed: {
      // colWidth () {
      //   return Number(this.width.replace("%", "")) * 0.24.toFixed(1)
      // }
    },
    mounted () {
      if (typeof this.label === 'undefined') {
        this.labelWidth = '0px'
      }
    }
  }
</script>

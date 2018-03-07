<template>
  <div class="data-analysis-block">
    <p>{{title}}</p>
    <p class="number">{{number}}</p>
    <p class="compare">较前天相比：<i :class="[compareDayIconClass]"></i><span>{{compareDay}}</span></p>
    <p class="compare">较上周同天：<i :class="[compareWeekIconClass]"></i><span>{{compareWeek}}</span></p>
  </div>
</template>

<script>
export default {
  props: {
    title: String,
    number: [Number, String],
    compareDay: String,
    compareWeek: String,
    compareDayTrend: String,
    compareWeekTrend: String
  },
  data () {
    return {
      compareDayIconClass: '',
      compareWeekIconClass: ''
    }
  },
  created () {
    this.initDayTrend()
    this.initWeekTrend()
  },
  watch: {
    compareDayTrend () {
      this.initDayTrend()
    },
    compareWeekTrend () {
      this.initWeekTrend()
    }
  },
  methods: {
    initDayTrend () {
      if (this.compareDayTrend === 'up') {
        this.compareDayIconClass = 'el-icon-sort-up sort-green'
      } else if (this.compareDayTrend === 'down') {
        this.compareDayIconClass = 'el-icon-sort-down sort-red'
      }
    },
    initWeekTrend () {
      if (this.compareWeekTrend === 'up') {
        this.compareWeekIconClass = 'el-icon-sort-up sort-green'
      } else if (this.compareWeekTrend === 'down') {
        this.compareWeekIconClass = 'el-icon-sort-down sort-red'
      }
    }
  }
}
</script>

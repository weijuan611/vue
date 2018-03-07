<template>
  <div style="display: inline-block;margin-right: 10px;">
    <el-radio-group v-model="dayMark" @change="changeDayMark">
      <el-radio-button label="昨天" v-show="dates.indexOf('昨天') > -1"></el-radio-button>
      <el-radio-button label="前天" v-show="dates.indexOf('前天') > -1"></el-radio-button>
      <el-radio-button label="最近7天" v-show="dates.indexOf('最近7天') > -1"></el-radio-button>
      <el-radio-button label="最近30天" v-show="dates.indexOf('最近30天') > -1"></el-radio-button>
    </el-radio-group>

    <template v-if="hasDateRange">
      <el-date-picker
          v-model="dateTime"
          type="daterange"
          value-format="yyyy-MM-dd"
          @change="changeDateTime"
          align="left"
          unlink-panels
          :picker-options="pickerOptions"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期">
      </el-date-picker>

      <el-checkbox v-show="hasCompare" v-model="compare" @change="changeCompare">对比</el-checkbox>

      <el-date-picker
          v-model="dateTime2"
          v-show="hasCompare && compare"
          type="daterange"
          value-format="yyyy-MM-dd"
          @change="changeDateTime2"
          align="left"
          unlink-panels
          :picker-options="pickerOptions"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期">
      </el-date-picker>
    </template>

    <template v-else>
      <el-date-picker
        v-model="datetime"
        type="date"
        placeholder="选择日期">
      </el-date-picker>
    </template>

  </div>

</template>

<script>
import { getDates, getDateRange }  from './../../assets/js/baseFunc/baseSelfFunc'

export default {
  props: {
    dates: {
      type: String,
      default: ['昨天', '前天', '最近7天']
    },
    hasDateRange: {
      type: Boolean,
      default: true
    },
    hasCompare: {
      type: Boolean,
      defallt: false
    },
    reset: {
      type: Boolean
    }
  },
  data () {
    return {
      dayMark: '昨天',
      dateTime: [getDates('yesterday'), getDates('yesterday')],
      dateTime2: [getDates('daybefore'), getDates('daybefore')],
      showDateTime2: false,
      compare: false,
      pickerOptions: {}
    }
  },
  watch: {
    reset () {
      this.dayMark = '昨天'
      this.changeDayMark('昨天')
    }
  },
  created () {
    this.pickerOptions = {
      shortcuts: [
         {
           text: '今天',
           onClick(picker) {
             let start = new Date()
             picker.$emit('pick', [start, start])
           }
         },
         {
           text: '昨天',
           onClick(picker) {
             let start = new Date()
             start.setTime(start.getTime() - 3600 * 1000 * 24 * 1)
             picker.$emit('pick', [start, start])
           }
         },
         // {
         //   text: '前天',
         //   onClick(picker) {
         //     let start = new Date()
         //     start.setTime(start.getTime() - 3600 * 1000 * 24 * 2)
         //     picker.$emit('pick', [start, start])
         //   }
         // },
         {
           text: '过去7天',
           onClick(picker) {
             const end = new Date()
             const start = new Date()
             start.setTime(start.getTime() - 3600 * 1000 * 24 * 6)
             picker.$emit('pick', [start, end])
           }
         },
         {
           text: '过去30天',
           onClick(picker) {
             const end = new Date()
             const start = new Date()
             start.setTime(start.getTime() - 3600 * 1000 * 24 * 29)
             picker.$emit('pick', [start, end])
           }
         },
         {
           text: '上周',
           onClick(picker) {
             picker.$emit('pick', getDateRange('上周'))
           }
         },
         {
           text: '本周',
           onClick(picker) {
             picker.$emit('pick', getDateRange('本周'))
           }
         },
         {
           text: '上月',
           onClick(picker) {
             picker.$emit('pick', getDateRange('上月'))
           }
         },
         {
           text: '本月',
           onClick(picker) {
             picker.$emit('pick', getDateRange('本月'))
           }
         },
         {
           text: '去年',
           onClick(picker) {
             picker.$emit('pick', getDateRange('去年'))
           }
         },
         {
           text: '今年',
           onClick(picker) {
             picker.$emit('pick', getDateRange('今年'))
           }
         },
         {
           text: '全部',
           onClick(picker) {
             picker.$emit('pick', ['', ''])
           }
         }
      ]
    }
    this.listenChange()
  },
  methods: {
    changeDayMark (val) {
      switch (val) {
        case '前天':
            this.dateTime = [getDates('daybefore'), getDates('daybefore')]
            this.dateTime2 = [getDates('fourDay'), getDates('fourDay')]
            break
        case '昨天':
            this.dateTime = [getDates('yesterday'), getDates('yesterday')]
            this.dateTime2 = [getDates('daybefore'), getDates('daybefore')]
            break
        case '最近7天':
            this.dateTime = [getDates('sevenDay'), getDates('today')]
            this.dateTime2 = [getDates('fourteenDay'), getDates('eightDay')]
            break
        case '最近30天':
            this.dateTime = [getDates('thirtyDay'), getDates('today')]
            this.dateTime2 = [getDates('sixtyDay'), getDates('thirty-one-Day')]
            break
      }
      this.listenChange()
    },
    changeCompare () {
      this.listenChange()
    },
    changeDateTime (val) {
      if (val !== null) {
        if (val[0] === getDates('yesterday') && val[1] === getDates('yesterday')) {
          this.dayMark = '昨天'
        } else if (val[0] === getDates('sevenDay') && val[1] === getDates('today')) {
          this.dayMark = '最近7天'
        } else if (val[0] === getDates('thirtyDay') && val[1] === getDates('today')) {
          this.dayMark = '最近30天'
        } else {
          this.dayMark = ''
        }
      } else {
        this.dayMark = ''
      }
      this.listenChange()
    },
    changeDateTime2 () {
      this.listenChange()
    },
    listenChange () {
      if (this.hasCompare) {
        this.$emit('receiveChange', {compare: this.compare, dateTime: this.dateTime, dateTime2: this.compare ? this.dateTime2 : null})
      } else {
        this.$emit('receiveChange', {dateTime: this.dateTime})
      }
    }
  }
}
</script>

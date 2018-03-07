<template>
  <el-dialog
    width="550px"
    title="追踪对比"
    @close="handleTraceCompareCancel"
    :visible.sync="openTraceCompareDialog">

    <el-form size="mini">
      <el-form-item>
        <el-checkbox-group v-model="copyFilterTraceCompareArray">
            <template v-for="items in copyFilterTraceCompare">
                <el-checkbox :label="items.value" :key="items.value">
                    &nbsp;比<span style="display:inline-block;width: 70px;margin-right: 10px;margin-left: 3px;">{{items.label}}</span>
                    <el-select v-model="items.media" size="mini" style="width: 150px;">
                        <el-option
                            v-for="item in mediaOptions"
                            :key="item"
                            :label="item"
                            :value="item">
                        </el-option>
                    </el-select>
                    <span style="margin: 0 10px;">排名</span>
                    <el-select v-model="items.rank" style="width: 150px;">
                        <el-option
                                v-for="item in contrastOptions"
                                :key="item"
                                :label="item"
                                :value="item">
                        </el-option>
                    </el-select>
                </el-checkbox>
                <br/>
            </template>
        </el-checkbox-group>
      </el-form-item>
    </el-form>

    <div slot="footer">
      <el-button size="mini" type="primary" plain @click="onClear">清空</el-button>
      <el-button size="mini" @click="handleTraceCompareCancel">取 消</el-button>
      <el-button size="mini" type="primary" @click="handleTraceCompareConfirm">确 定</el-button>
    </div>

  </el-dialog>
</template>

<script>
  export default {
    props: {
      show: {
        type: Boolean,
        default: false
      },
        click: {
        type: Boolean,
        default: false
      },
    },
    data () {
      return {
        openTraceCompareDialog: false,
        mediaOptions: ['- - - - -', 'PC端', 'M端'],
        contrastOptions: ['- - - - -', '高', '低', '持平'],
        initTraceCompare: [
          {
              check: false,
              value: 0,
              label: '名字1',
              media: '---',
              rank: '---'
          },
          {
              check: false,
              value: 1,
              label: '名字2',
              media: '---',
              rank: '---'
          },
          {
              check: false,
              value: 2,
              label: '名字3',
              media: '---',
              rank: '---'
          }
        ],
        filterTraceCompare: [],
        copyFilterTraceCompare: [],
        copyFilterTraceCompareArray: []
      }
    },
    watch: {
      show () {
        this.openTraceCompareDialog = this.show
      },
      click() {
          this.initClassList()
      }
    },
    mounted () {
//      this.initClassList()
    },
    methods: {
      switchObjArrayToArray (objArray) {
        return objArray.filter(f => f.check === true).map(m => m.value)
      },
      initClassList () {
          console.log('ajslkdfjlaksd')
          let that = this
          this.$ajax.get('/lexicon/opponent/init')
              .then(function (res) {
                  if (res.data !== undefined) {
                      that.initTraceCompare = res.data.initTraceCompare
                      that.filterTraceCompare = JSON.parse(JSON.stringify(that.initTraceCompare))
                      that.copyFilterTraceCompare = JSON.parse(JSON.stringify(that.initTraceCompare))
                      that.copyFilterTraceCompareArray = that.switchObjArrayToArray(that.copyFilterTraceCompare)
                  }
              })
              .catch(function (err) {
              })
      },
      onClear () {
        this.copyFilterTraceCompareArray = []
        this.copyFilterTraceCompare = this.initTraceCompare
      },
      handleTraceCompareCancel () {
        this.copyFilterTraceCompare = JSON.parse(JSON.stringify(this.filterTraceCompare))
        this.copyFilterTraceCompareArray = this.switchObjArrayToArray(this.filterTraceCompare)
        this.$emit('close')
      },
      handleTraceCompareConfirm () {
        for (let i = 0; i < this.copyFilterTraceCompare.length; i++) {
          if (this.copyFilterTraceCompareArray.indexOf(this.copyFilterTraceCompare[i].value) > -1) {
            this.copyFilterTraceCompare[i].check = true
          } else {
            this.copyFilterTraceCompare[i].check = false
          }
        }
        this.filterTraceCompare = JSON.parse(JSON.stringify(this.copyFilterTraceCompare))
        console.log('this.copyFilterTraceCompareArray', this.copyFilterTraceCompareArray)
        this.$emit('close')
      }
    }
  }
</script>

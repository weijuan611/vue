<template>
  <el-dialog
    width="500px"
    title="评价"
    @close="handleAssessCancel"
    :visible.sync="openAssessDialog">

    <el-form ref="index-form" size="mini" style="margin-left: 30px;">
      <el-form-item>
          <el-checkbox v-model="assessTerms.term1.check">
            <span>{{assessTerms.term1.label}}</span>
            <el-select v-model="assessTerms.term1.media" style="width: 120px;margin: 0 10px;">
              <el-option
                v-for="item in mediaOptions"
                :key="item"
                :label="item"
                :value="item">
              </el-option>
            </el-select>
            是
            <el-select v-model="assessTerms.term1.assess" style="width: 120px;margin: 0 10px;">
              <el-option
                v-for="item in assessOptions"
                :key="item"
                :label="item"
                :value="item">
              </el-option>
            </el-select>
          </el-checkbox>
      </el-form-item>

      <el-form-item>
        逻辑条件
        <el-select v-model="assessTerms.logic" style="width: 120px;margin: 0 10px;">
          <el-option
            v-for="item in logicOptions"
            :key="item"
            :label="item"
           :value="item">
          </el-option>
        </el-select>
      </el-form-item>

      <el-form-item>
        <el-checkbox v-model="assessTerms.term2.check">
          <span>{{assessTerms.term2.label}}</span>
          <el-select v-model="assessTerms.term2.media" style="width: 120px;margin: 0 10px;">
            <el-option
              v-for="item in mediaOptions"
              :key="item"
              :label="item"
              :value="item">
            </el-option>
          </el-select>
          是
          <el-select v-model="assessTerms.term2.assess" style="width: 120px;margin: 0 10px;">
            <el-option
              v-for="item in assessOptions"
              :key="item"
              :label="item"
              :value="item">
            </el-option>
          </el-select>
        </el-checkbox>
      </el-form-item>
    </el-form>

    <div slot="footer">
      <el-button size="mini" type="primary" plain @click="onClear">清空</el-button>
      <el-button size="mini" @click="handleAssessCancel">取 消</el-button>
      <el-button size="mini" type="primary" @click="handleAssessConfirm">确 定</el-button>
    </div>

  </el-dialog>
</template>

<script>
  export default {
    props: {
      show: {
        type: Boolean,
        default: false
      }
    },
    data () {
      return {
        openAssessDialog: false,
        mediaOptions: ['- - - - -', 'PC评价', 'M评价'],
        assessOptions: ['- - - - -', '已达标', '推广中', '未推广'],
        logicOptions: ['- - - - -', '且', '或'],
        initAssessTerms: {
          term1: {
            check: false,
            label: '条件1',
            media: '- - - - -',
            assess: '- - - - -',
          },
          logic: '- - - - -',
          term2: {
            check: false,
            label: '条件2',
            media: '- - - - -',
            assess: '- - - - -',
          }
        },
        assessTerms: {},
        copyAssessTerms: {}
      }
    },
    watch: {
      show () {
        this.openAssessDialog = this.show
      }
    },
    created () {
      this.assessTerms = JSON.parse(JSON.stringify(this.initAssessTerms))
      this.copyAssessTerms = JSON.parse(JSON.stringify(this.initAssessTerms))
    },
    methods: {
      onClear () {
        this.assessTerms = JSON.parse(JSON.stringify(this.initAssessTerms))
//        console.log('this.initAssessTerms', this.initAssessTerms)
//        console.log('this.assessTerms', this.assessTerms)
      },
      handleAssessCancel () {
        this.assessTerms = JSON.parse(JSON.stringify(this.copyAssessTerms))
        this.$emit('close')
      },
      handleAssessConfirm () {
        this.copyAssessTerms = JSON.parse(JSON.stringify(this.assessTerms))
        this.$emit('close')
        this.$emit('onSubmit',this.copyAssessTerms)
      }
    }
  }
</script>

<template>
    <!-- dialog选择分类开始 -->
    <el-dialog
            width="835px"
            title="选择分类"
            class="class-list-dialog"
            :modal="false"
            @close="openFilterClass = false"
            :visible.sync="openFilterClass">
        <div class="class-list clearfix">
            <ul class="one">
                <li class="all">全行业</li>
                <li class="a">0-18岁培训</li>
                <li class="b">成人培训</li>
                <li class="c">学历文凭</li>
            </ul>
            <ul class="two">
                <li class="all">搜索类别</li>
                <template v-for="item in classList">
                    <li :key="item.id" :id="item.id" :title="item.value"
                        :class="[item.value === showClassDetail ? 'selectClassTypeTrue' : 'selectClassTypeFalse']"
                        @click="showClassDetail = item.value">
                        {{item.value}}
                        <span class="triggle" v-show="item.value === showClassDetail"></span>
                    </li>
                </template>
            </ul>
            <template v-for="items in classList">
                <ul class="three" v-show="items.value === showClassDetail">
                    <li class="all">{{items.value}}</li>
                    <template v-for="item in items.children">
                        <li :title="item.value">
                                <span class="left" :key="item.id" :id="item.id"
                                      :class="[item.id === copyFilterClass.id ? 'selectClassOneTrue' : 'selectClassOneFalse']"
                                      @click="copyFilterClass = item">{{item.value}}</span>
                            <ul class="right">
                                <template v-for="i in item.children">
                                    <li :title="i.value" :key="i.id" :id="i.id"
                                        :class="[i.id === copyFilterClass.id ? 'selectClassTwoTrue' : 'selectClassTwoFalse']"
                                        @click="copyFilterClass = i">{{i.value}}
                                    </li>
                                </template>
                            </ul>
                        </li>
                    </template>
                </ul>
            </template>
        </div>
        <div slot="footer">
            <el-button size="mini" @click="openFilterClass = false">取 消</el-button>
            <el-button size="mini" type="primary" @click="handleFilterClassConfirm">确 定</el-button>
        </div>
    </el-dialog>
</template>
<script>
    export default {
        props:{
            cId:{
                type:Number,
                default:0
            },
            handle:{
                type:Boolean,
                default:false
            }
        },
        watch:{
            cId:function (data,old) {
                console.log(data)
            },
            handle:function (data,old) {
                this.openFilterClass= true;
            }
        },
        mounted(){
            let that = this
            this.$ajax.get('common/dialog/keywordCategory').then(function (res) {
                if(res.data !== undefined){
                    that.classList = res.data
                }
            })
        },
        data(){
            return {
                openFilterClass:false,
                classList:{},
                showClassDetail:'',
                copyFilterClass:{}
            }
        },
        methods:{
            handleFilterClassConfirm:function(){
                this.openFilterClass = false
                this.$emit('callback',this.copyFilterClass)
            }
        }
    }
</script>
<style scoped>
    @import "./../../assets/css/page/lexicon.css";
</style>
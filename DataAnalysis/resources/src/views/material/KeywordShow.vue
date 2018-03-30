<template>
    <div>
        <table class="table1" cellspacing="0">
            <tr>
                <td style="width: 28%;">
                    <b>关键词：</b>{{form.keyword}}
                </td>
                <td>
                    <b>分类：</b>{{form.category}}
                </td>
                <td>
                    <b>已发文章：</b>{{form.article_num}}
                </td>
            </tr>
            <tr style="height: 40px;">
                <td >
                    <b>类型：</b>
                    <span v-show="form.type === 1">新闻</span>
                    <span v-show="form.type === 2">头条</span>
                    <span v-show="form.type === 3">知道</span>
                </td>
                <td>
                    <span v-show="form.type === 1"><b>地区：</b>{{form.area_name}}</span>
                    <span v-show="form.type === 3"><b>悬赏积分：</b>{{form.points}}&nbsp;(分)</span>
                </td>
                <td>
                    <b>来源作者：</b>{{form.author}}
                </td>
            </tr>
            <tr>
                <td>
                    <b>标题：</b>{{form.title}}
                </td>
                <td colspan="2">
                    <b>检测结果：</b>&nbsp;
                    <span class="red">{{result}}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id="show-content" style="height: 250px;"></div>
                    <img v-show="form.cover_img !== ''" :src="form.cover_img" style="display: inline-block;width:150px;height: 150px;vertical-align: middle;" />
                </td>
            </tr>
        </table>

        <articles :handle="articlesShow" @callback="articlesCallback"/>
        <areas :handle="areaHandle" @callback="areaSelect"/>
        <keyword-category :handle="categoryShow" @callback="categoryCallback"/>
        <keyword-radio :handle="keywordRadioShow" @callback="keywordCallback"/>
    </div>
</template>

<script>
    import KeywordCategory from './../../components/dialog/KeywordCategory.vue'
    import KeywordRadio from './../../components/dialog/KeywordRadio.vue'
    import ueditor from './../../components/base/ueditor.vue'
    import Areas from './../../components/dialog/Area.vue'
    import Articles from './../../components/dialog/Articles.vue'
    import { mapState } from 'vuex'
    export default {
        components: {
            ueditor,Articles,KeywordCategory,KeywordRadio,Areas
        },
        computed: {
            ...mapState('pagePassValue', [
                'taskDetail_to_article'
            ])
        },
        watch: {
            taskDetail_to_article () {
                this.getFormData()
            }
        },
        mounted(){
            this.getFormData();
        },
        data () {
            return {
                result:'',
                areaHandle:false,
                courseHandle:false,
                articlesShow:false,
                categoryShow:false,
                keywordRadioShow:false,
                load:false,
                form:{
                    ka_id:0,
                    km_id:0,
                    td_id:0,
                    kw_id:0,
                    keyword:'',
                    c_id:0,
                    category:'',
                    url:'',
                    use_num:0,
                    title:'',
                    author:'',
                    cover_img:'',
                    content:'',
                    type:1,
                    area_id:0,
                    area_name:'',
                    points:0,
                },
                integralOptions: [
                    {
                        value: '0',
                        label: '无悬赏'
                    }, {
                        value: '10',
                        label: '10'
                    }, {
                        value: '20',
                        label: '20'
                    }, {
                        value: '30',
                        label: '30'
                    }, {
                        value: '40',
                        label: '40'
                    }, {
                        value: '50',
                        label: '50'
                    }, {
                        value: '70',
                        label: '70'
                    }, {
                        value: '100',
                        label: '100'
                    }
                ],
                insertedMsg: {
                    insertMsgBool: false,
                    msg: ''
                },
                time: {}
            }
        },
        destroyed () {
            clearTimeout(this.time)
        },
        methods: {
            categoryCallback:function(data){
                this.form.c_id = data.id
                this.form.category = data.value
            },
            keywordCallback:function (data) {
                this.form.kw_id = data.kw_id
                this.form.keyword = data.keyword
            },
            getFormData () {
                let that = this
                let kw_id = this.taskDetail_to_article.kw_id
                let td_id = this.taskDetail_to_article.td_id
                let km_id = this.taskDetail_to_article.km_id
                let div = document.getElementById('show-content');
                if (typeof(kw_id) !== undefined) {
                    that.form.kw_id = kw_id
                    that.$ajax.get('material_input/info?kw_id='+kw_id).then(function (res) {
                        if(res.status === 200){
                            that.form.keyword = res.data.keyword;
                            that.form.c_id = res.data.c_id;
                            that.form.category = res.data.category;
                            that.form.article_num = res.data.article_num;
                        }
                    }).catch(function (res) {
                        that.$message({type:'error',message:res.data})
                    })
                }
                if(km_id >0){
                    that.form.km_id = km_id
                    that.$ajax.get('material_keywordlist/info?km_id='+km_id).then(function (res) {
                        if(res.status === 200){
                            that.form.title = res.data.title;
//                            that.insertedMsg = {insertMsgBool: !that.insertedMsg.insertMsgBool,msg:res.data.content};
                            that.form.content = res.data.content;
                            that.form.author = res.data.author;
                            that.form.cover_img = res.data.cover_img;
                            that.form.type = res.data.type;
                            that.form.area_id = res.data.area_id;
                            that.form.area_name = res.data.AreaName;
                            that.form.points = res.data.points;
                            that.form.url = res.data.url;
                            that.form.use_num = res.data.use_num;
                            div.innerHTML = that.form.content;
                        }
                    }).catch(function (res) {
                        that.$message({type:'error',message:res})
                    })
                }

                if (typeof(td_id) !== undefined){
                    that.form.td_id = td_id
                }
            },
            getUEMsg (msg) {
                this.form.content = msg
            },
            removeHTMLFunc () {
                this.insertedMsg = {insertMsgBool: !this.insertedMsg.insertMsgBool,msg:this.filterAllTags(this.form.content)}
            },
            areaSelect(data){
                this.form.area_id = data.area_id;
                this.form.area_name = data.area_name;
            },
            courseSelect(data){
                this.form.course_id = data.Id
                this.form.course_name = data.Name
                this.form.school_id = data.LoginId
            },
            insertKeywordFunc (keyword) {
                let msgInfo
                if (typeof keyword == "object") {
                    msgInfo = this.form.keyword + this.form.content
                } else {
                    msgInfo = keyword +this.form.content
                }
                this.insertedMsg = {
                    insertMsgBool: !this.insertedMsg.insertMsgBool,
                    msg: msgInfo
                }
            },
            filterAllTags (str) {
                str = str.replace(/<\/?[^>]*>/g,''); //去除HTML tag
                str = str.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
                str = str.replace(/\n[\s| | ]*\r/g,'\n'); //去除多余空行
                str = str.replace(/&nbsp;/ig, '');  //去掉所有空格
                return str
            },
            saveMaterial(){
                let that = this
                that.$ajax.post('material_keywordlist/edit',that.form).then(function (res) {
                    if(res.data.type === 'success'){
                        that.$message.success('保存成功！')
                    }else{
                        that.$message({'type':'error',message:res.data.message})
                    }
                }).catch(function(res){
                    that.$message.error('网络错误')
                })
            },
            releaseMaterial(){
                let that = this
                that.$ajax.post('article_input/save_release',that.form).then(function (res) {
                    if(res.data.error === 0){
                        that.form.ka_id = res.data.data;
                        that.$message('保持发布成功')
                    }else{
                        that.form.ka_id = res.data.data;
                        that.result = res.data.message
                        that.$message({type:'error',message:'发布失败'})
                    }
                }).catch(function(res){
                    that.$message.error('网络错误！')
                })
            },
            uploadCover(response, file, fileList){
                this.form.cover_img = response
            },
            handleClose () {
                this.$emit('close', false)
            },
            articlesCallback:function (data) {
                this.form.title = data.title
                this.form.author = data.author
                this.form.type = String(data.type)
                this.form.content = data.content
                this.form.cover_img = data.cover_img
                this.form.area_id = data.area_id
                this.form.points = data.points
                this.insertKeywordFunc(data.content)
            }
        }
    }
</script>

<style>
    .el-dialog__body {
        padding: 10px 20px;
    }
    .el-upload-list {
        vertical-align: middle;
        display: inline-block;
        margin-bottom: 5px;
    }
    .table1 {
        font-size: 15px;
    }
</style>

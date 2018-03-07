<template>
    <div class="login-wrap" id="home">
        <div class="title"></div>
        <div class="box">
          <div class="left">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="0px" class="demo-ruleForm">
                <el-form-item prop="username">
                    <el-input v-model="ruleForm.username" placeholder="请输入用户名/手机号码">
                      <i slot="prefix" class="el-input__icon el-icon-user"></i>
                    </el-input>
                </el-form-item>
                <el-form-item prop="password">
                    <el-input type="password" placeholder="请输入密码" v-model="ruleForm.password" @keyup.enter.native="submitForm('ruleForm')">
                      <i slot="prefix" class="el-input__icon el-icon-pass"></i>
                    </el-input>
                </el-form-item>
                <div class="submit">
                    <el-button type="primary" @click="submitForm('ruleForm')">登录</el-button>
                </div>
            </el-form>
          </div>
          <div class="right">
            <div class="img"></div>
          </div>
        </div>
        <div class="footer">
          Copyright © 2018 Houxue.com 版权所有 厚学网
        </div>
        <!-- <canvas id="canvas"></canvas> -->
    </div>
</template>

<script>

    export default {
        data: function () {
            return {
                ruleForm: {
                    username: '',
                    password: ''
                },
                rules: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'blur'},
                        {min:5,message:'用户名必须大于5位',trigger:'blur,change'}
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                        {min:6, message: '密码必须大于6位',trigger: 'blur,change'}
                    ]
                }
            }
        },
        mounted () {
          // this.drawCanvas()
        },
        methods: {
            // drawCanvas () {
            //   var canvas = document.getElementById('canvas')
            //   canvas.width = window.innerWidth;
            //   canvas.height = window.innerHeight;
            //   var ctx = canvas.getContext('2d')
            //   ctx.lineWidth = .3;
            //   ctx.strokeStyle = (new Color(150)).style;
            //
            //   var mousePosition = {
            //     x:  canvas.width / 2 - 50,
            //     y:  canvas.height / 2 - 30
            //   };
            //
            //   var dots = {
            //     nb: 60,
            //     distance: 150,
            //     d_radius: 150,
            //     array: []
            //   };
            //
            //   function colorValue(min) {
            //     return Math.floor(Math.random() * 255 + min);
            //   }
            //
            //   function createColorStyle(r,g,b) {
            //     return 'rgba(' + r + ',' + g + ',' + b + ', 0.8)';
            //   }
            //
            //   function mixComponents(comp1, weight1, comp2, weight2) {
            //     return (comp1 * weight1 + comp2 * weight2) / (weight1 + weight2);
            //   }
            //
            //   function averageColorStyles(dot1, dot2) {
            //     var color1 = dot1.color,
            //     color2 = dot2.color;
            //
            //     var r = mixComponents(color1.r, dot1.radius, color2.r, dot2.radius),
            //     g = mixComponents(color1.g, dot1.radius, color2.g, dot2.radius),
            //     b = mixComponents(color1.b, dot1.radius, color2.b, dot2.radius);
            //     return createColorStyle(Math.floor(r), Math.floor(g), Math.floor(b));
            //   }
            //
            //   function Color(min) {
            //     min = min || 0;
            //     this.r = colorValue(min);
            //     this.g = colorValue(min);
            //     this.b = colorValue(min);
            //     this.style = createColorStyle(this.r, this.g, this.b);
            //   }
            //
            //   function Dot(){
            //     this.x = Math.random() * canvas.width;
            //     this.y = Math.random() * canvas.height;
            //
            //     this.vx = -.5 + Math.random();
            //     this.vy = -.5 + Math.random();
            //
            //     this.radius = Math.random() * 2;
            //
            //     this.color = new Color();
            //   }
            //
            //   Dot.prototype = {
            //     draw: function(){
            //       ctx.beginPath();
            //       ctx.fillStyle = this.color.style;
            //       ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2, false);
            //       ctx.fill();
            //     }
            //   };
            //
            //   function createDots(){
            //     for(var i = 0; i < dots.nb; i++){
            //       dots.array.push(new Dot());
            //     }
            //   }
            //
            //   function moveDots() {
            //     for(var i = 0; i < dots.nb; i++){
            //
            //       var dot = dots.array[i];
            //
            //       if(dot.y < 0 || dot.y > canvas.height){
            //         dot.vx = dot.vx;
            //         dot.vy = - dot.vy;
            //       }
            //       else if(dot.x < 0 || dot.x > canvas.width){
            //         dot.vx = - dot.vx;
            //         dot.vy = dot.vy;
            //       }
            //       dot.x += dot.vx;
            //       dot.y += dot.vy;
            //     }
            //   }
            //
            //   function connectDots() {
            //     for(var i = 0; i < dots.nb; i++){
            //       for(var j = 0; j < dots.nb; j++){
            //         var i_dot = dots.array[i];
            //         var j_dot = dots.array[j];
            //
            //         if((i_dot.x - j_dot.x) < dots.distance && (i_dot.y - j_dot.y) < dots.distance && (i_dot.x - j_dot.x) > - dots.distance && (i_dot.y - j_dot.y) > - dots.distance){
            //           if((i_dot.x - mousePosition.x) < dots.d_radius && (i_dot.y - mousePosition.y) < dots.d_radius && (i_dot.x - mousePosition.x) > - dots.d_radius && (i_dot.y - mousePosition.y) > - dots.d_radius){
            //             ctx.beginPath();
            //             ctx.strokeStyle = averageColorStyles(i_dot, j_dot);
            //             ctx.moveTo(i_dot.x, i_dot.y);
            //             ctx.lineTo(j_dot.x, j_dot.y);
            //             ctx.stroke();
            //             ctx.closePath();
            //           }
            //         }
            //       }
            //     }
            //   }
            //
            //   function drawDots() {
            //     for(var i = 0; i < dots.nb; i++){
            //       var dot = dots.array[i];
            //       dot.draw();
            //     }
            //   }
            //
            //   function animateDots() {
            //     ctx.clearRect(0, 0, canvas.width, canvas.height);
            //     moveDots();
            //     connectDots();
            //     drawDots();
            //
            //     requestAnimationFrame(animateDots);
            //   }
            //
            //   //----------------------跟着鼠标动--------------------
            //   // document.getElementById('home').addEventListener('mousemove', function(e){
            //   //   mousePosition.x = e.pageX;
            //   //   mousePosition.y = e.pageY;
            //   // });
            //
            //   // document.getElementById('home').addEventListener('mouseleave', function(e){
            //   //   mousePosition.x = canvas.width / 2;
            //   //   mousePosition.y = canvas.height / 2;
            //   // });
            //   //----------------------跟着鼠标动--------------------
            //
            //   createDots();
            //   requestAnimationFrame(animateDots);
            // },
            submitForm (formName) {
                const self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.$ajax.post('/login',self.ruleForm)
                            .then(function(res){
                                if(res.data !== false){
                                    localStorage.setItem('ms_username',res.data);
                                    self.$router.push('/index.html')
                                }else{
                                    self.$message.error('账号或密码错误！')
                                }
                            })
                            .catch(function(err){
                                self.$message('网络错误！请联系管理员')
                            });
                    } else {
                        return false
                    }
                })
            }
        }
    }
</script>

<style scoped>
     /*@import "../assets/css/page/login.css"; */


    .login-wrap {
      width: 100%;
      height: 100%;
      background: #0a78d3 url(./../assets/img/login_bg.jpg) no-repeat center center;
      background-size: 100% 100%;
    }
    .title {
      height: 30%;
      padding-top: 100px;
      width: 100%;
      text-align: center;
      background: url(./../assets/img/login_title.png) no-repeat center 70%;
      background-size: 18%;
    }
    .box {
      max-width: 660px;
      min-width: 500px;
      width: 42%;
      max-height: 310px;
      min-height: 280px;
      height: 38%;
      margin: 0 auto;
      background-color: #cee6f2;
      border-radius: 3px;
    }
    .el-icon-user {
      background: url(./../assets/img/login_icons.png) no-repeat 4px center;
    }
    .el-icon-pass {
      background: url(./../assets/img/login_icons.png) no-repeat -18px center;
    }
    .box .left, .box .right {
      float: left;
      width: 50%;
      height: 100%;
    }
    .box .left .el-form {
      padding: 60px 15px 60px 30px;
    }
    .box .left .el-form .el-form-item {
      margin-bottom: 10%;
    }
    .box .left .submit {
      text-align: center;
      margin-top: 15%;
    }
    .box .left .submit button {
      width: 100%;
      height: 36px;
      letter-spacing: 5px;
      background-color: #36a9e6;
    }
    .box .right .img {
      width: 80%;
      height: 70%;
      margin: 15% auto;
      background: url(./../assets/img/login_right_img.png) no-repeat center center;
      background-size: 100%;
    }
    .footer {
      color: #99d5e7;
      font-size: 13px;
      width: 100%;
      height: 120px;
      text-align: center;
      position: absolute;
      bottom: 0;
    }

  /* .login-wrap {
    width: 100%;
    height: 100%;
    background: #17282d;
    background-size: 100% 100%;
    position: absolute;
    top: 0;
    z-index: 2;
  }
  .title {
    padding-top: 150px;
    padding-bottom: 50px;
    width:100%;
    text-align: center;
    font-size: 30px;
    color: #fff;
  }
  .box {
    box-sizing: content-box;
    width: 300px;
    height: 160px;
    padding: 40px;
    border-radius: 5px;
    background: #fff;
    margin: 0 auto;
  }
  .submit {
    text-align: center;
  }
  .submit button {
    width: 100%;
    height: 36px;
  }
  .footer {
    color: #99d5e7;
    font-size: 13px;
    width: 100%;
    height: 120px;
    text-align: center;
    position: absolute;
    bottom: 0;
  }
  canvas {
    position: absolute;
    top: 0;
    z-index: -1;
  } */
</style>

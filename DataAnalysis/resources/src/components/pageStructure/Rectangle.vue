<template>
    <div class="rect" v-show="show">
      <p v-show="iconType === 'colorBall1' || iconType === 'colorBall2'" class="colorBall1" :style="iconStyle">{{words}}</p>
	    <p v-show="iconType === 'colorBall2'" class="colorBall2">{{words}}</p>
      <p v-show="iconType === 'longwords'" class="longwords">{{words}}</p>
      <ul :style="ulStyle">
        <template v-for="item in data">
          <li :style="liStyle">
            <p>{{item.title || item.name}}</p>
            <p>{{item.number || item.value || 0}}</p>
          </li>
        </template>
      </ul>
    </div>
</template>

<script>
	export default {
	  props: {
      show: {
        type: Boolean,
        default: true
      },
	    data: {
	      type: Array
	    },
	    cols: {
	      type: [Number, String],
	      default: 5
	    },
	    words: {
	      type: String,
	      default: ''
	    },
      iconType: {
        type: String,
        default: ''
      },
      iconBg: {
        type: String,
        default: 'lightblue'
      },
      iconFontColor: {
        type: String,
        default: '#fff'
      },
      iconWidth: {
        type: String,
        default: '0px'
      }
	  },
	  data () {
	  	return {
        iconStyle: {
          backgroundColor: this.iconBg,
          color: this.iconFontColor
        },
        ulStyle: {
          paddingLeft: this.iconWidth
        },
	  		liStyle: {
	  			width: 100 / this.cols + '%'
	  		}
	  	}
	  }
	}

</script>

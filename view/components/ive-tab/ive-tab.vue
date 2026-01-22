<template>
  <view :class="{tab_fixed: fixed}">
    <view class="tab" :class="[border? '': 'no_border']" :style="{top: (windowTop) + 'px' }">
      <view
        @click="clickTab(index)"
        v-for="(item, index) in tabArr"
        :key="index"
        :class="['tab_item', current == index?'active_tab': '', !scroll? 'shrink': '']"
      >
        <view :class="['tab_text', item.disable? 'disable': '']">{{item.name||item.text || item.label}}</view>
        <text class="dot" :style="{display: item.dot&&item.dot>0? 'block': 'none'}"></text>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * @emit onClick->点击tab的事件
 */
/**
 * @props current  当前所在索引
 * @props border  是否显示下边框
 * @props shrink
 */
export default {
  name: "ive-tab",
  props: {
    tabArr: {
      type: Array,
      default() {
        return [{ name: "text" }];
      },
    },
    current: {
      type: Number,
      default: 0,
    },
    border: {
      type: Boolean,
      default: true,
    },
    scroll: {
      type: Boolean,
      default: true,
    },
    fixed: {
      type: Boolean,
      default: false,
    }
  },
  data() {
    return {
      windowTop: 0
    };
  },
  created() {
    uni.getSystemInfo({
      success: (res) => {
        this.windowTop = res.windowTop;
      },
    });
  },
  methods: {
    clickTab(index) {
      if (this.tabArr[index].disable) {
        return;
      }
      this.$emit("onClick", index);
    },
  },
};
</script>

<style lang="scss" scoped>
@mixin flex($position: flex-start) {
  display: flex;
  align-items: center;
  justify-content: $position;
}

@mixin fc($position:flex-start) {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: $position;
}
.tab_fixed{
  position: relative;
  height: 44px;
  .tab{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
  }
}
.tab {
  width: 100%;
  height: 44px;
  flex-shrink: 0;
  font-size: 16px;
  @include flex(flex-start);
  background: #fff;
  overflow-x: auto;
  border-bottom: 1rpx solid rgba(255, 255, 255, 0.2);
  -webkit-overflow-scrolling: touch;
  .disable{
    color: #999;
  }
  .tab_item {
    flex-shrink: 0;
    position: relative;
    padding: 0 4px;
    @include fc(center);
    width: 30%;
    height: 100%;
    flex-grow: 1;
    .dot {
      position: absolute;
      display: block;
      width: 5px;
      height: 5px;
      right: 5px;
      top: 0px;
      // padding: 2px 5px;
      background: #ff6000;
      border-radius: 100px;
      color: #fff;
      font-size: 10px;
    }
    &::after {
      content: "";
      display: block;
      position: absolute;
      left: 50%;
      bottom: 0;
      margin-left: -18px;
      width: 0;
      height: 3px;
      background: transparent;
      border-radius: 3px;
      transition: all 0.3s ease;
    }
  }
  .shrink {
    flex-shrink: 1;
  }
  .active_tab {
    color: #ff6000;
    &::after {
      width: 36px;
      background: #ff6000;
    }
  }
}
.no_border {
  border: 0;
}
</style>

### 标签切换

* 按钮支持事件参数 click,hover
* 动画方向 left,up,fadein,0 为无动画
* 支持 prev,next按钮
* 支持页面打开默认显示`#锚点`指向内容

[demo](http://madeby83.com/demo/Jquery-rTabs-master/demo.html)

```
$("#tabauto").rTabs({
    defaultShow:0,   //默认显示第几个 true为根据#锚点显示
    prev:'#prev',    //前一个id
    next:'#next',    //后一个id
    btnClass:'.j-tab-nav',  /*按钮的父级Class*/
    conClass:'.j-tab-con',  /*内容的父级Class*/
    bind:'hover',   /*事件参数 click,hover*/
    animation:'0',  /*动画方向 left,up,fadein,0 为无动画*/
    speed:300,  /*动画运动速度*/
    delay:200,  /*Tab延迟速度*/
    auto:true,  /*是否开启自动运行 true,false*/
    autoSpeed:3000  /*自动运行速度*/
});
```

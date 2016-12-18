{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <link rel="stylesheet" type="text/css" href="http://www.timemeet.cn/static/admin/css/reset.css">  
    <link rel="stylesheet" type="text/css" href="http://www.timemeet.cn/static/admin/css/main.css">
</head>
<body>
    <div class="system-message">
        <?php switch ($code) {?>
        <?php case 1:?>
        <div id="container">
            <div id="stage" class="stage">
                <div id="clouds" class="stage" style="background-position: 516.599999999995px 0px;"></div>
            </div>

            <div id="ticket">
                <section id="ticket_left">
                    <p class="text1_a"><?php echo(strip_tags($msg));?></p>
                    <p class="text2_a">已找到航班</p>
                    <p class="text3_a">MH370</p>
                    <p class="text5_a">从</p>
                    <p class="text6_a">地球</p>
                    <p class="text7_a">到</p>
                    <p class="text8_a">火星</p>           
                    <p class="text9_a">座</p>
                    <p class="text10_a">oo</p>
                    <p class="text11_a">尝试另一次飞行</p>
                    <nav class="text12_a">
                        <ul>
                            <li>......</li>
                        </ul>
                    </nav>          
                </section>

                <section id="ticket_right">
                    <p class="text1_b">航班号</p>
                    <p class="text2_b">MH370</p>
                    <p class="text3_b">从</p>
                    <p class="text4_b">地球</p>
                    <p class="text5_b">到</p>
                    <p class="text6_b">火星</p>
                    <p class="text7_b">座</p>
                    <p class="text8_b">oo</p>
                    <p class="text9_b">1</p>
                    <p class="text10_b">103076498</p>
                </section>
            </div>
        </div>
        <?php break;?>
        <?php case 0:?>
        <div id="container">
            <div id="stage" class="stage">
                <div id="clouds" class="stage" style="background-position: 516.599999999995px 0px;"></div>
            </div>

            <div id="ticket">
                <section id="ticket_left">
                    <p class="text1_a"><?php echo(strip_tags($msg));?></p>
                    <p class="text2_a">未找到航班</p>
                    <p class="text3_a">MH370</p>
                    <p class="text5_a">从</p>
                    <p class="text6_a">地球</p>
                    <p class="text7_a">到</p>
                    <p class="text8_a">火星</p>           
                    <p class="text9_a">座</p>
                    <p class="text10_a">xx</p>
                    <p class="text11_a">尝试另一次飞行</p>
                    <nav class="text12_a">
                        <ul>
                            <li>......</li>
                        </ul>
                    </nav>          
                </section>

                <section id="ticket_right">
                    <p class="text1_b">航班号</p>
                    <p class="text2_b">MH370</p>
                    <p class="text3_b">从</p>
                    <p class="text4_b">地球</p>
                    <p class="text5_b">到</p>
                    <p class="text6_b">火星</p>
                    <p class="text7_b">座</p>
                    <p class="text8_b">xx</p>
                    <p class="text9_b">1</p>
                    <p class="text10_b">103076498</p>
                </section>
            </div>
        </div>
        <?php break;?>
        <?php } ?>

        <p class="detail"></p>
        <p class="jump">
            页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </p>
</div>
<script src="http://www.timemeet.cn/static/admin/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="http://www.timemeet.cn/static/admin/js/jquery.spritely-0.5.js" type="text/javascript"></script>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
        href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
    (function($) {
        $(document).ready(function() {
            $('#clouds').pan({fps: 40, speed: 0.7, dir: 'right', depth: 10});
        });
    })(jQuery);
</script>
</body>
</html>

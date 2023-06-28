<?php 

require_once("../config.php");
require('helper/helper.php');

$signatureId = $_GET['id'];
$signature = getSignature($signatureId);

// 權限檢查
/* 為了讓知道連結就可以簽名 2020/11/30 移除檢查權限
require_login();
checkSignatureRole();
*/
if (empty($signature)){
    die('找不到該授權書');
}

if (!empty($signature->signature)){
	die('該授權同意書已簽名完畢');
}

if (!empty($signature->attach_file)){
    die('該簽名已上傳附加檔案');
}

global $now;
$now = new DateTime();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$newSignature = [
		'speech_date' => $_POST['speech_date'],
		'location' => $_POST['location'],
		'title' => $_POST['title'],
		'is_share' => isset($_POST['is_share']) ? $_POST['is_share'] : 1,
        'is_download' => isset($_POST['is_download']) ? $_POST['is_download'] : 1,
		'other_limit' => $_POST['other_limit'],
		'idno' => $_POST['idno'],
		'signature' => $_POST['signature']
	];
    
    /*
    if ($signature->is_paid == 1){
        $newSignature['paid_fee'] = $_POST['paid_fee'];
    }
    */
    if ($signature->language == 'en'){
        $require = ['speech_date', 'location', 'title', 'signature'];
    }else{
        $require = ['speech_date', 'location', 'title', 'idno', 'signature'];
    }
	
	foreach($require as $key){
		if (empty($newSignature[$key])){
			die('發生錯誤');
		}
	}
	$result = updateSignature($signature->id, $newSignature);
	if ($result){
        if ($signature->language == 'en'){
            $message = "Signature uploaded successfully";
        }else{
            $message = "上傳簽章成功";
        }
		
		echo "<script type=\"text/javascript\">
				alert('{$message}');
				location.href=\"/elearn/fetSignature/index.php\";
			  </script>";
	}	
}

function getSignature($id)
{
	global $DB;
	$sql = "SELECT * FROM mdl_fet_signature WHERE tmp_key = :id";
	$params = ['id' => $id];
	return $DB->get_record_sql($sql, $params);
}

function updateSignature($id, $newSignature)
{
	global $now;
	global $DB;

	$newSignature['updated_at'] = $now->format('Y-m-d H:i:s');
	$newSignature['upload_date'] = $now->format('Y-m-d');

    $setQuery = join(', ', array_map(function($value, $key){
        return $key.' = :'.$key;
    }, $newSignature, array_keys($newSignature)));
	$sql = "UPDATE mdl_fet_signature
			SET {$setQuery}
			WHERE id = :id";
    $newSignature['id'] = $id;                        
	return $DB->execute($sql, $newSignature);
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>著作利用授權同意書</title>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<style type="text/css">
    #canvas {}
    
    #canvasDiv {
        background-color:gray;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
    }		
	</style>
</head>
<body>
	
<div  style="margin: auto;border: 1px solid #000;padding: 0px 25px 25px 25px;">
	<form id="signature_form" method="POST">
        <?php 
        $language = ($signature->language == 'en') ? '_en' : '';
        if ($signature->is_paid == 0){
            require "view/free{$language}.php";
        }elseif($signature->is_paid == 1){
            require "view/paid{$language}.php";
        }
        ?>
	<div id="signatureArea" style="padding-bottom:10px;">
		<div id="canvasDiv" ></div>
	</div>	
	<div>
		<input type="hidden" id="signature" name="signature">
		<button id="btn_clear" type="button" class="btn  btn-success">
            <?php if($signature->language == 'en'): ?>
            Clear
            <?php else: ?>
            清除
            <?php endif ?>
        </button>
	    <button  id="btn_submit" type="button" class="btn  btn-primary">
            <?php if($signature->language == 'en'): ?>
            Send
            <?php else: ?>
            簽名上傳
            <?php endif ?>
        </button>
    </div>
    </form>			
</div>
</body>
<script type="text/javascript">
    var language = '<?=$signature->language?>';
    $('#signatureArea').on('touchmove', function (event) {
        event.preventDefault();
    });
    var canvasDiv = document.getElementById('canvasDiv');
    var canvas = document.createElement('canvas');
    var screenwidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;

    var canvasWidth = screenwidth * 0.95;
    var canvasHeight = 300;
    document.addEventListener('touchmove', onDocumentTouchMove, false);
    var point = {};
    point.notFirst = false;
    canvas.setAttribute('width', canvasWidth);
    canvas.setAttribute('height', canvasHeight);
    canvas.setAttribute('id', 'canvas');
    canvasDiv.appendChild(canvas);
    if (typeof G_vmlCanvasManager != 'undefined') {
        canvas = G_vmlCanvasManager.initElement(canvas);
    }
    var context = canvas.getContext("2d");
    var img = new Image();
    img.src = "Transparent.png";

    img.onload = function() {
        var ptrn = context.createPattern(img, 'repeat');
        context.fillStyle = ptrn;
        context.fillRect(0, 0, canvas.width, canvas.height);
        //context.strokeStyle="#0000FF";
    }
    canvas.addEventListener("touchstart", function(e) {
        //console.log(e);
        var mouseX = e.touches[0].pageX - this.offsetLeft;
        var mouseY = e.touches[0].pageY - this.offsetTop;
        paint = true;
        addClick(e.touches[0].pageX - this.offsetLeft, e.touches[0].pageY - this.offsetTop);
        //console.log(e.touches[0].pageX - this.offsetLeft, e.touches[0].pageY - this.offsetTop);
        redraw();
    });

    canvas.addEventListener("touchend", function(e) {
        //console.log("touch end");
        paint = false;
    });

    canvas.addEventListener("touchmove", function(e) {
        if (paint) {
            //console.log("touchmove");
            addClick(e.touches[0].pageX - this.offsetLeft, e.touches[0].pageY - this.offsetTop, true);
            //console.log(e.touches[0].pageX - this.offsetLeft, e.touches[0].pageY - this.offsetTop);
            redraw();
        }

    });

    canvas.addEventListener("mousedown", function(e) {
        var mouseX = e.pageX - this.offsetLeft;
        var mouseY = e.pageY - this.offsetTop;
        paint = true;
        addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
        redraw();
    });
    canvas.addEventListener("mousemove", function(e) {
        if (paint) {
            addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
            redraw();
        }
    });
    canvas.addEventListener("mouseup", function(e) {
        paint = false;
    });
    canvas.addEventListener("mouseleave", function(e) {
        paint = false;
    });
    document.getElementById("btn_clear").addEventListener("click", function() {
        canvas.width = canvas.width;
    });
    document.getElementById("btn_submit").addEventListener("click", function() {
        var check=$("input[name='signature_auth[]']:checked").length;//判斷有多少個方框被勾選
        var error_message = '';
        var message = '';
        console.log(isCanvasBlank(canvas));

        if(isCanvasBlank(canvas)){
            if (language == 'en'){
                error_message = 'Please Sign';
            }else{
                error_message = '請簽名後再送出';
            }
            alert(error_message);
            return false;
        }else if ($('input[name=idno]').val() == '' && language != 'en'){
        	alert('請填寫身分證');
        	return false;
        }else if ($('input[name=speech_date]').val() == ''){
            if (language == 'en'){
                error_message = 'Date is required';
            }else{
                error_message = '請填寫演講日期';
            }
            alert(error_message);
        	return false;     
        }else if ($('input[name=location]').val() == ''){
            if (language == 'en'){
                error_message = 'Location is required';
            }else{
                error_message = '請填寫演講地點';
            }            
        	alert(error_message);
        	return false; 
        }else if ($('input[name=title]').val() == ''){
            if (language == 'en'){
                error_message = 'Topic of presentation is required';
            }else{
                error_message = '請填寫演講地點';
            }            
            alert(error_message);
        	return false;             	            	       	
        }else{
            if (language == 'en'){
                message = 'You can’t re-sign after uploading, press (OK) to send, press (Cancel) to return to the original screen.';
            }else{
                message = "上傳後即無法重新簽名，按【確定】即送出 按【取消】回到原畫面";
            }               
            var go=confirm(message);
            if(go==true){
                document.getElementById("signature").value = canvas.toDataURL("image/png");
                document.getElementById("signature_form").submit();
                console.log(canvas.toDataURL("image/png")); 
            }else{
                return false;
            }
            
        }            
    });

    function onDocumentTouchStart(event) {
        if (event.touches.length == 1) {
            event.preventDefault();
            // Faking double click for touch devices
            var now = new Date().getTime();
            if (now - timeOfLastTouch < 250) {
                reset();
                return;
            }
            timeOfLastTouch = now;
            mouseX = event.touches[0].pageX;
            mouseY = event.touches[0].pageY;
            isMouseDown = true;

        }

    }

    function onDocumentTouchMove(event) {

        if (event.touches.length == 1) {
            event.preventDefault();
            mouseX = event.touches[0].pageX;
            mouseY = event.touches[0].pageY;
        }
    }

    function onDocumentTouchEnd(event) {
        if (event.touches.length == 0) {
            event.preventDefault();
            isMouseDown = false;
        }
    }

    var clickX = new Array();
    var clickY = new Array();
    var clickDrag = new Array();
    var paint;

    function addClick(x, y, dragging) {
        clickX.push(x);
        clickY.push(y);
        clickDrag.push(dragging);
    }

    function redraw() {

        //canvas.width = canvas.width; // Clears the canvas
        //context.strokeStyle = "#df4b26";
        context.strokeStyle = "#0000ff";
        context.lineJoin = "round";
        context.lineWidth = 2;
        while (clickX.length > 0) {
            point.bx = point.x;
            point.by = point.y;
            point.x = clickX.pop();
            point.y = clickY.pop();
            point.drag = clickDrag.pop();
            context.beginPath();
            if (point.drag && point.notFirst) {
                context.moveTo(point.bx, point.by);
            } else {
                point.notFirst = true;
                context.moveTo(point.x - 1, point.y);
            }
            context.lineTo(point.x, point.y);
            context.closePath();
            context.stroke();
        }
    }

    function isCanvasBlank(canvas) {
      return !canvas.getContext('2d')
        .getImageData(0, 0, canvas.width, canvas.height).data
        .some(channel => channel !== 0);
    }

</script>
</html>

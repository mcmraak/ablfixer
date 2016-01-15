<?php
session_start();

if(!isset($_GET['201345']) && $_SESSION['auth']!='true'){
    die();
} else {
    $_SESSION['auth'] = 'true';
}
class core
{
    public $method, $data;
    public function out($method,$data=""){
        $this->method = $method;
        $this->data = $data;
        return $this->$method();
    }
    ############## SCAN FUNCTIONS
    
    public function find_regexp($file){
        $filecode = file_get_contents($file);
        $patterns = array();
        $patterns['Запутывание!'] = '/\$[a-zA-Z0-9]{2,3}\[[0-9]\]\.\$[a-zA-Z0-9]{2,3}\[[0-9]\]/';
        $patterns['Запутывание2!'] = '/\$.*=\$.*\[.*\].*\..*\$.*=\$.*\[.*\]/';
        $patterns['Перенаправление'] = '/'.preg_quote('header("Location:').'/';
        $patterns['EVAL'] = '/'.preg_quote('eval(').'/';
        $patterns['Обфускация'] = '/[a-zA-Z0-9]{100,}/';
        $patterns['Сокрытие'] = '/'.preg_quote('base64').'/';
        $patterns['Фрейм'] = '/'.preg_quote('<iframe').'/';
        $patterns['ШЕЛЛ! (@ignore)'] = '/'.preg_quote('@ignore_user_abort(true)').'/';
        $patterns['Загрузчик!'] = '/'.preg_quote('@move_uploaded_file').'/';
        $patterns['Скрытая ссылка!'] = '/'.preg_quote('Array(').'\'\d+\',\'\d+\'/';
        $patterns['ШЕЛЛ! (full access)'] = '/'.preg_quote('extract(array("default_action"').'/';
        $patterns['Блокировка ошибок'] = '/'.preg_quote('error_reporting(').'/';
        
        foreach ($patterns as $key=>$pattern){
            preg_match($pattern, $filecode, $matches, PREG_OFFSET_CAPTURE);
            if($matches[0][1]>0){
                $type .= $key.", ";
            }
        }
        $type = substr($type, 0, -2);
        return $type;

    }
    
    ############## FUNCTIONS
    public function GetDirFilesR($dir){   
            $dir_iterator = new RecursiveDirectoryIterator($dir);
            $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
            return $iterator;
            }
    
    public function filelist($dir){
            $scaner = $this->GetDirFilesR($dir);
            $count = count($scaner);
            foreach($scaner as $v){
            if($v->isFile()){
                $v = substr($v, 2);
                $return .= "<file>$v</file>";
                }
            }
            return $return;
    }
    
    
    ############## FRONT
    
    public function savereport(){
        $path = $_SERVER['HTTP_HOST']."_abl-fixer_report.txt";        
        $text = join("\r\n", $this->data);
        
        $faa = fopen($path, "w");
        fwrite($faa, $text);
        fclose($faa);
        return "Файл отчёта сохранён. <a href='$path'>Ссылка на файл отчёта</a>";
    }
    
    public function openfile(){
        $code = file_get_contents($this->data);
        return $code;
    }
    
    public function savefile(){
        $text = $this->data;
        $path = $_POST['pathfile'];
        $path = str_replace("\\", "/", $path);
        $faa = fopen($path, "w");
        fwrite($faa, $text);
        fclose($faa);
        return "Файл: [$path] Исправлен.";
    }
    
    public function delfile(){
        $path = $this->data;
        $path = str_replace("\\", "/", $path);
        if(file_exists($path)){
            unlink($path);
            return "Файл: [$path] Удалён!";
        }
    }
    
    public function autor(){
        return "<nocleanws/><script type='text/javascript'>alert('Автор: Александр Аблизин (mcmraak@gmail.com)');</script>";
    }
    
    public function scan(){
        
        $out = "<div class='infobox'>Опрос дериктории, это может занять некоторое время...<script type='text/javascript'>ajax('scan_proccess');</script></div>";
        return $out;
    }
    
    public function backscan(){
        $filelist = $this->GetDirFilesR("./");
        foreach($filelist as $v){
            if($v->isFile()){
                $v = substr($v, 2);
                //$return .= $v;
                $this->data = $v;
                $return .= $this->scanfile();
                }
            }
        return $return;
    }
    
    public function scan_proccess(){
        $filelist = $this->filelist("./");
        $out  = "<div id='filelist' style='display:none'>".$filelist."</div>";
        $out .= "<div id='scanwin' class='infobox'>"
                . "<div id='progressinfo'></div>";
        $out .= "<script type='text/javascript'>frontscan();</script>";
        $out .= '<div class="progressdiv">
                 <div id="scanprogress" class="proline"></div>
                </div>
                <div id="result"></div>';
        $out .= "</div>";
        return $out;
    }
    
    public function scanfile(){
        $fp = $this->data;
        if($fp!=""){
        if (filesize($fp)>1048576){
            return "<div class='find warning'>Файл $fp пропущен так как его размер больше 1мб.</div>";
        } else {
            
            $entry = 0;
            $scan_file = $this->find_regexp($fp);
            
            if($scan_file!=""){
                
                $colors = array(
                    "Фрейм" => "#D865FF"
                );
                
                $ex = explode('.',$fp);
                $ex = $ex[count($ex)-1];
                $ext_color = array(
                    "js" => "#5669CA",
                    "css" => "#549368"
                );
                $ext_color = $ext_color[$ex];
                
                if(strpos($scan_file, "!")>-1){
                    $danger = "<span class='danger'>Опасность!</span>";
                } else {
                    $danger = "";
                }
                
                return "<div types='$scan_file' style='border-left-color:".$colors[$scan_file].";color:$ext_color' class='find virused' path='$fp'>Подозрение:[ $fp ] $danger</div>";
            }
        }
    }
    } // IF filename == ""
}


if(isset($_GET['method'])){
    $method = $_GET['method'];
    $data =   $_POST['data'];
    $render = new core; 
    die($render->out($method,$data));
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>AUDIT TOOL</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="" />
       <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    </head>
    <style type="text/css">
        body, html {
        height: 100%;
        }
        body{
            background: #F5FAFF;
        }
        #wrapper {
            width: 1000px;
            margin: 0 auto;
            padding-bottom: 370px;
        }
        #mainmenu {
            height: 30px;
            background: #6C81A1;
            padding: 6px;
            padding-left: 6%;
            margin-bottom: 13px;
        }
        #mainmenu ul {
            margin: 0;
            padding: 0;
        }
        #mainmenu li {
            color: #fff;
            font-family: Verdana;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        #mainmenu li:hover {
            color: #ffff00;
        }
        #mainmenu .vrt li {
            float: left;
            list-style: none;
            margin-right: 6%;
        }
        #mainmenu .hrz {
            position: absolute;
            display: none;
            padding: 10px;
            background: #9DAEC8;
        }
        #mainmenu .hrz li {
            float: none;
            white-space: nowrap;
        }
        .infobox {
            background: rgba(169, 169, 169, 0.09);
            padding: 20px;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.11);
            margin-top: 10px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .progressdiv {
            height: 20px;
            background: #cccccc;
        }
        .proline {
            height: 20px;
            background: #7992E0;
            float: left;
        }
        .alarm {
            color: #F00;
        }
        .find {
            background: #fff;
            border: 1px solid #EAEAEA;
            font-size: 13px;
            padding: 0 12px;
            border-radius: 3px;
            margin-bottom: 2px;
            cursor: pointer;
        }
        .find:hover {
            background: #FEFFD5;
        }
        .virused {
            border-left: 10px solid #F00;
        }
        .warning {
            border-left: 10px solid #FFD500;
        }
        .moreinfo {
            display: none;
            padding: 2px 9px;
            margin: 2px 0;
            background: #EEF5FA;
            border-radius: 9px;  
        }
        .cbtn {
            text-decoration: underline;
            cursor: pointer;
        }
        .editfile {
            color: #6C6CFF;
        }
        .editfile:hover {
            color: #0000FF;
        }
        .delfile {
            color: #FF6C6C;
        }
        .delfile:hover {
            color: #F00;
        }
        #editwin {
            display: none;
            position: fixed;
            height: 400px;
            left: 0;
            right: 0;
            bottom: 0;
            background: #6C81A1;
        }
        #code {
            width: 100%;
            height: 328px;
            font-family: tahoma;
            font-size: 12px;
            font-weight: bold;
            color: #464562;
        }
        #savecode {
            float: right;
            margin-right: 10px;
        }
        #pathinfo {
            padding: 9px;
            padding-left: 10px;
            font-size: 12px;
            font-family: verdana;
            color: #fff;
            height: 34px;
        }
        #close_edit_win {
            position: fixed;
            right: 7px;
            bottom: 370px;
        }
        #report {
            width: 1000px;
            margin: 0 auto;
            margin-top: 10px;
            padding: 10px;
            font-size: 12px;
            font-family: verdana;
            color: #1E19DB;
            background: #EDF1F6;
            border-radius: 4px;
            border: 1px solid #D5D5D5;
            display: none;
        }
        .report_line {
            
        }
        #result {
            margin-top: 16px;
            font-family: verdana;
            font-size: 12px;
            color: #64729D;    
        }
        #reportpan {
            padding: 6px;
            padding-left: 12px;
            background: #D7D7D7;
            border-radius: 5px;
            margin-bottom: 12px;
        }
        #modal_win {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.64);
        }
        #modal_win .close_modal{
            float: right;
            color: #1A00FF;
/*            font-size: 12px;*/
            text-decoration: underline;
            cursor: pointer;
        }
        #modal_win_wrapper {
            width: 90%;
            min-height: 90%;
            margin: 10px auto;
            background: #fff;
        }
        #modal_win_title {
            padding: 0 17px;
        }
        .danger {
            background: #F00;
            color: #fff;
            padding: 2px 5px;
            border-radius: 7px;
            font-weight: bold;
            font-family: verdana;
            font-size: 10px;
            margin-left: 10px;
        }
    </style>
    
    <body>
        <div id="mainmenu">
            <ul class="vrt">
                <li >Сканировать
                    <ul class="hrz">
                        <li method="scan">Сканировать frontend</li>
                        <li method="backscan">Сканировать backend</li>
                    </ul>
                </li>
                <li>О проекте
                    <ul class="hrz">
                        <li method="autor">Автор</li>
                        <li>Проверка версии</li>
                    </ul>
                </li>
            </ul>
        </div>
        <div id="wrapper">
            <div id="workspace"></div>
            <div id="report"><div id='reportpan'><button id='savereport'>Сохранить отчёт в файл</button></div></div>
        </div>
        <div id="editwin">
            <button id="close_edit_win">закрыть</button>
            <div id="pathinfo"></div>
            <textarea id="code">
                
            </textarea>
            <div id="controlpan"><button id="savecode">Сохранить</button></div>
        </div>
        <div id="modal_win">
            <div id="modal_win_wrapper">
                <div id="modal_win_title">
                    <span class="filepath"></span><span class="close_modal">Закрыть</span>
                </div>
                <iframe id="frontframe" src="/"  width="100%" style="min-height: 500px"></iframe>
            </div>
        </div>
        
    </body>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

<script type="text/javascript">
    
    function add_report(str){
        $("#report").show();
        var x = "<div class='report_line'>"+str+"</div>";
        $("#report").append(x);
    }
    
    
    $(document).on("mouseenter", "#mainmenu li", function(){
        if($(this).children("ul").size()>0){
            $("> .hrz",this).slideDown(100);
        }
    });
    $(document).on("mouseleave", ".hrz", function(){
            $(this).hide();
    });
    
    function ajax(method,data){
        if(data == "") {
        
        if (typeof method == 'function') {
            data = eval(method)();
        }}
        
       $.ajax({
        type: "POST",
        url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?method="+method,  
        cache: false,
        data: {'data':data},
        success: function(x){
            if(x.indexOf("nocleanws")<1){
                $("#workspace").text("");
            }
            $("#workspace").append(x);
            }  
        });//.ajax 
    }
    
    $(document).on("click", "[method]", function(){
        var method = $(this).attr("method");
        ajax(method);
        $(".hrz").hide();
    });
    
    $(document).on("click", ".virused", function(){
        
        if($(this).children(".moreinfo").size()<1){
        var types = $(this).attr("types");
        $(this).append("<div class='moreinfo'>"+types+" [<span class='cbtn editfile'>Редактировать</span>] [<span class='cbtn novirus'>Пропустить</span>] [<span class='cbtn fronttest'>Посмотреть вывод</span>] [<span class='cbtn delfile'>Удалить</span>]</div>");
        $(".moreinfo").slideDown(100);} else {
         $(this).children(".moreinfo").remove();
        }
    });
    

    $(document).on("click", ".editfile", function(event){
        event.stopPropagation();
        var path = $(this).parent().parent().attr("path");
        $(this).parent().parent().attr("editnow","1");
        $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?method=openfile",  
            cache: false,
            data: {'data':path},
            success: function(x){
                $("#pathinfo").text("Путь файла: "+path);
                $("#code").val("");
                $("#code").val(x);
                $("#editwin").show();
                $("#savecode").attr("pathfile",path);
                }  
            });//.ajax
    
    });
    
    $(document).on("click", ".novirus", function(event){
        event.stopPropagation();
        var path = $(this).parent().parent().attr("path");
        $(this).parent().parent().remove();
        add_report("Файл: ["+path+"] Проверен.");
    });
    
    $(document).on("click", ".fronttest", function(event){
        event.stopPropagation();
        var path = $(this).parent().parent().attr("path");
        var h = $(window).height();
            h = (h*90)/100;
        
        $("#frontframe").attr("height",h+"px");
        $("#modal_win .filepath").text(path);
        $("#frontframe").attr("src",path);
        $("#modal_win").fadeIn();
        
    });
    
    
    $(document).on("click", "#savecode", function(){
        
        var code = $("#code").val();
        var path = $(this).attr("pathfile");
        
        $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?method=savefile",  
            cache: false,
            data: {'data':code,'pathfile':path},
            success: function(x){
                add_report(x);
                }  
            });
            $("#code").val("");
            $("#editwin").hide();
            $("[editnow]").remove();
    });
    
    $(document).on("click", ".delfile", function(event){
        
        event.stopPropagation();
        var path = $(this).parent().parent().attr("path");

        $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?method=delfile",  
            cache: false,
            data: {'data':path},
            success: function(x){
                add_report(x);
                }  
            });
        $(this).parent().fadeOut(300);  
        $(this).parent().parent().remove();
        $("#editwin").hide();   
    });
    
    $(document).on("click", "#close_edit_win", function(){
        $("#editwin").hide();
    });
    
    $(document).on("click", "#modal_win .close_modal", function(){
        $("#modal_win").hide();
    });
    
      function frontscan(){
        var c = $("file").size();
        $("#progressinfo").text("Количество файлов: "+c);
        var i = 0;
        var entries = 0;
        function rec_scan(){
            if(i<c+1){
            var file = $("file").eq(i).text();

            $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?method=scanfile",  
            cache: false,
            data: {'data':file},
            success: function(x){
                if(file!='ablfixer.php'){
                    $("#workspace").append(x);
                    if(x!=""){
                     entries++;
                     $("#result").text("Вхождений найдено: "+entries);
                    }    
                }
                $("#progressinfo").text("Отсканировано "+i+" из "+c);
                var width = (i*100)/c;
                $("#scanprogress").css("width",width+"%");

                i++;
                rec_scan();
                }  
            });//.ajax}
            } else {
                $("#result").text("Сканирование завершено. Вхождений найдено: "+entries);
            }
        }
        rec_scan();
      }
      
      $(document).on("click", "#savereport", function(event){
        
        var c = $(".report_line").size();
        var text = [];
        for(var i=0;i<c;i++){
            text[i] = $(".report_line").eq(i).text();
        }

        $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?method=savereport",  
            cache: false,
            data: {'data':text},
            success: function(x){
                $("#reportpan").hide();
                add_report(x);
                }
        });
    });
      
</script>

</html>
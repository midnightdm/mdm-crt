<!DOCTYPE html>
<html>
<head>
  <title>Clinton River Traffic <?php echo $title; ?></title>
  <title>Clinton River Traffic</title>  
  <meta name="description" content= "river fans barge tow spotters riverboats paddlewheelers steamboats Clinton Iowa MMSI transponder data map text alerts" />
<meta name="robots" content= "index, follow">
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL');?>css/header2.css" type="text/css">
  <link rel="stylesheet" href="<?php echo getEnv('BASE_URL').$main['css'];?>" type="text/css">
  <script src="<?php echo getEnv('BASE_URL');?>js/jscookmenu.min.js"></script>
<script type="text/javascript">
var cmMenuBar1 =
{
   mainFolderLeft: '',
   mainFolderRight: '',
   mainItemLeft: '',
   mainItemRight: '',
   folderLeft: '',
   folderRight: '',
   itemLeft: '',
   itemRight: '',
   mainSpacing: 0,
   subSpacing: 0,
   delay: 100,
   offsetHMainAdjust: [0, 0],
   offsetSubAdjust: [0, 0]
};
var cmMenuBar1HSplit = [_cmNoClick, '<td class="MenuBar1MenuSplitLeft"><div></div></td>' +
                                    '<td class="MenuBar1MenuSplitText"><div></div></td>' +
                                    '<td class="MenuBar1MenuSplitRight"><div></div></td>'];
var cmMenuBar1MainVSplit = [_cmNoClick, '<div><table width="15" cellspacing="0"><tr><td class="MenuBar1HorizontalSplit">|</td></tr></table></div>'];
var cmMenuBar1MainHSplit = [_cmNoClick, '<td colspan="3" class="MenuBar1MainSplitText"><div></div></td>'];
document.addEventListener('DOMContentLoaded', function(event)
{
   cmDrawFromText('MenuBar1', 'hbl', cmMenuBar1, 'MenuBar1');
});
</script>
</head>
<body>
  <div id="wrapper">
    <div id="header">
      <?php $this->load->view('header2'); ?>
    </div>
    <div id="main">
      <?php $this->load->view($main['view']); ?>
    </div>
    <div id="footer">
      <?php $this->load->view('footer'); ?>
    </div>
  </div>  
</body>
</html>
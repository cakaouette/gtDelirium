<?php ob_start(); ?>
<h4>Les 3 guildes</h4>
<div class="row align-items-center">
<?php foreach ($v_info as $guildId => $info) { ?>
  <div class="col-md-3">
    <div class="card card-<?=$info["guild"]->getColor()?>">
      <div class="card-header">
        <h3 class="card-title"><?=$info["guild"]->getName()?></h3>
      </div>
      <!-- /.card-header -->
      <div class="card-body">
        Nombre de membres : <?=$info["memberNumber"]?> /30<br>
        Score du raid précédent : <?=$info["lastRank"]?>
      </div>
       <!-- /.card-body -->
    </div>
  </div>
<?php } ?>
<?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) { ?>
  <div class="col-sm-3">
    <a href="?page=alliance&subpage=addguild">
      <button type="button" class="btn btn-link bg-gradient-primary align-items-center col-md-6" href="#test">
        <i class="fa fa-plus"></i>
        Ajouter
      </button>
    </a>
  </div>
<?php } ?>
</div>
<h4><a href="https://discord.com/channels/756475890816385034" target="_blank">Le discord</a></h4>

<!-- Line chart -->
<div class="card card-<?php echo $_SESSION["guild"]["color"] ?? "info" ?> card-outline">
  <div class="card-header">
    <h3 class="card-title">
      <i class="far fa-chart-bar"></i>
      Classement aux raids
    </h3>

    <div class="card-tools">
    </div>
  </div>
  <div class="card-body">
    <div id="ranks-chart" style="height: 500px;"></div>
  </div>
  <!-- /.card-body-->
</div>
<!-- /.card -->


<!--lightblue-->
<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<!-- Page specific script -->
<!-- FLOT CHARTS -->
<script src="dist/AdminLTE-3.1.0/plugins/flot/jquery.flot.js"></script>
<!-- FLOT TIME PLUGIN - allows the chart to redraw when the window is resized -->
<script src="dist/AdminLTE-3.1.0/plugins/flot/plugins/jquery.flot.time.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script src="dist/AdminLTE-3.1.0/plugins/flot/plugins/jquery.flot.resize.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script src="dist/AdminLTE-3.1.0/plugins/flot/plugins/jquery.flot.pie.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/AdminLTE-3.1.0/dist/js/demo.js"></script>
<script>
  $(function () {

    /*
     * LINE CHART
     * ----------
     */
    
    <?php
        $rankTremens = Array(); $objectivTremens = Array(); $toSetTremens = false; $lastObjectivTremens = NULL;
        $rankNocturnum = Array(); $objectivNocturnum = Array(); $toSetNocturnum = false; $lastObjectivNocturnum = NULL;
        $rankChill = Array();
        
        foreach ($v_info[1]["ranks"] as $raidId => $info) {
          $rankTremens[] = $info["timestamp"].", ".$info["rank"];
        }
        $dataTremens = implode("],[", $rankTremens);
        
        foreach ($v_raids as $raidId => $timeRaid) {
          if (array_key_exists($raidId, $v_info[1]["objectives"])) {
            $objectiv = $v_info[1]["objectives"][$raidId]["rank"];
            if ($toSetTremens) {
              $objectivTremens[] = $timeRaid.", ".$lastObjectivTremens;
            }
            $objectivTremens[] = $timeRaid.", ".$objectiv;
            $lastObjectivTremens = $objectiv;
            $toSetTremens = false;
          } else {
            $toSetTremens = true;
          }
          
          if (array_key_exists($raidId, $v_info[2]["objectives"])) {
            $objectiv = $v_info[2]["objectives"][$raidId]["rank"];
            if ($toSetNocturnum) {
              $objectivNocturnum[] = $timeRaid.", ".$lastObjectivNocturnum;
            }
            $objectivNocturnum[] = $timeRaid.", ".$objectiv;
            $lastObjectivNocturnum = $objectiv;
            $toSetNocturnum = false;
          } else {
            $toSetNocturnum = true;
          }
        }
        if ($toSetTremens) {
          $objectivTremens[] = end($v_raids).", ".$lastObjectivTremens;
        }
        $dataObjectivTremens = implode("],[", $objectivTremens);
        if ($toSetNocturnum) {
          $objectivNocturnum[] = end($v_raids).", ".$lastObjectivNocturnum;
        }
        $dataObjectivNocturnum = implode("],[", $objectivNocturnum);
        
        
        foreach ($v_info[2]["ranks"] as $raidId => $info) {
          $rankNocturnum[] = $info["timestamp"].", ".$info["rank"];
        }
        $dataNocturnum = implode("],[", $rankNocturnum);
        
        foreach ($v_info[3]["ranks"] as $raidId => $info) {
          $rankChill[] = $info["timestamp"].", ".$info["rank"];
        }
        $dataChill = implode("],[", $rankChill);
        
        echo "var jsDataTremens = [[$dataTremens]];\n";
        echo "var jsDataNocturnum = [[$dataNocturnum]];\n";
        echo "var jsDataChill = [[$dataChill]];\n";
        
        echo "var jsDataObjectivTremens = [[$dataObjectivTremens]];\n";
        echo "var jsDataObjectivNocturnum = [[$dataObjectivNocturnum]];";
    ?>

    var tremens_objectiv = {
      data : jsDataObjectivTremens,
      color: '#ff0707',
      label: "Objectif Tremens",
      points: { show: false },
      lines: {lineWidth: 1}
    };
    var nocturnum_objectiv = {
      data : jsDataObjectivNocturnum,
      color: '#ff0707',
      label: "Objectif Nocturnum",
      points: { show: false },
      lines: {lineWidth: 1}
    };
    var line_data1 = {
      data : jsDataTremens,
      color: '#ffc107',
      label: "Tremens"
    };
    var line_data2 = {
      data : jsDataNocturnum,
      color: '#3c8dbc',
      label: "Nocturnum"
    };
    var line_data3 = {
      data : jsDataChill,
      color: '#01ff70',
      label: "Chill"
    };
    $.plot('#ranks-chart', [line_data1, line_data2, line_data3, tremens_objectiv, nocturnum_objectiv], {
      grid  : {
        hoverable  : true,
        borderColor: '#f3f3f3',
        borderWidth: 1,
        tickColor  : '#f3f3f3'
      },
      series: {
        shadowSize: 0,
        lines     : {
          show: true,
          lineWidth: 3
        },
        points    : {
          show: true
        }
      },
      lines : {
        fill : false,
        color: ['#3c8dbc', '#f56954']
      },
      yaxis : {
        show: true,
        tickDecimals: null,
        transform : function  ( v )  {  return - v  ;  }, //{  return  Math.log(v) ;  } , 
        inverseTransform : function  ( v ) {  return -v ;  } //{  return  Math.exp(v) ;  } 
      },
      xaxis : {
        show: true,
        mode: "time",
        timeBase : "seconds" ,
        timeformat : "%d/%m/%Y" 
      }
    });
    //Initialize tooltip on hover
    $('<div class="tooltip-inner" id="ranks-chart-tooltip"></div>').css({
      position: 'absolute',
      display : 'none',
      opacity : 0.8
    }).appendTo('body');
    $('#ranks-chart').bind('plothover', function (event, pos, item) {

      if (item) {
            y = item.datapoint[1].toFixed(0);

        $('#ranks-chart-tooltip').html(item.series.label + ' = ' + y + 'ème')
          .css({
            top : item.pageY + 5,
            left: item.pageX + 5
          })
          .fadeIn(200);
      } else {
        $('#ranks-chart-tooltip').hide();
      }

    });
    /* END LINE CHART */
  });
</script>
<?php $specScript = ob_get_clean(); ?>


<?php require('view/template.php'); ?>

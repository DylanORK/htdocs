
$().ready(function () {

      $(".Progres li").each(function(n) {
            $(this).addClass('couleur' + n);
            //$(this).attr("class", "couleur" + n);
      });

});
/*
var element = document.getElementById("Progres");
element.classList.add("couleur");





  var ul = document.getElementByclass("Progres");
var items = ul.getElementsByTagName("li");
for (var i = 0; i < items.length; ++i) {
  // do something with items[i], which is a <li> element
	
	
}



$('.Progres').click(function() {
  //  $('.menuitem').removeClass('active');
    //removes active class from all menu items

    $(this).addClass('couleur'+Math.floor((Math.random() * 15) + 1));
    //adds active class to clicked one
});
<html>
<!-- Author: HackTrack -->
<head>
	<title>Démo de sélection de couleur de fond</title>
	<script language="javascript" type="text/javascript">
		function changeBackgroundColor(elm){
		window.status=elm.style.backgroundColor;
			document.body.style.backgroundColor=elm.style.backgroundColor;
		}
	</script>
	<style>
		table, tr, td{
			margin: 0px;
			padding:0px;
			border: solid 1px #0f0f0f;
		}
		div{
			width: 50px;
			height: 50px;
		}
	</style>
</head>
<body>
	<table>
		<tr>
			<th colspan="4">Choisissez la couleur de fond</th>
		</tr>
		<tr>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #000000;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #111111;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #222222;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #333333;"></div></td>
		</tr>
		<tr>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #444444;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #555555;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #666666;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #777777;"></div></td>
		</tr>
		<tr>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #888888;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #999999;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #aaaaaa;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #bbbbbb;"></div></td>
		</tr>
		<tr>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #cccccc;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #dddddd;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #eeeeee;"></div></td>
			<td><div onclick="javascript: changeBackgroundColor(this);" style="background-color: #ffffff;"></div></td>
		</tr>			
	</table>
</body>
</html>

*/
<html>
<head>
<script>
var cost;
var sum;
var percent;
var year;
var month;
var first;
var permonth;
var last="init";
var lastButOne;
var details;
var recFields={"cost":1, "permonth":1,"sum":1,"first":1, "percent":1, "year":1, "month":1};
var fields=["cost", "sum", "year", "month", "first", "permonth", "percent"];

function init() {
  cost=document.getElementById("cost");
  sum=document.getElementById("sum");
  percent=document.getElementById("percent");
  year=document.getElementById("year");
  month=document.getElementById("month");
  first=document.getElementById("first");
  permonth=document.getElementById("permonth");

	percent.value=11;
 	year.value=15;
  month.value=0;
  cost.value=<?= $sum ?>;
  first.value=cost.value*0.2;

  recount();
}

function round() {
for (var e in fields) {
	var value=document.getElementById(fields[e]).value;
  document.getElementById(fields[e]).style.color="#000000";
  if (isNaN(value)||value==Infinity||value<0) {
  	if (document.getElementById(fields[e]).id==last) {
	  	document.getElementById(fields[e]).value="";
   	}else {
  		document.getElementById(fields[e]).value="?";
    	document.getElementById(fields[e]).style.color="#FF0000";
    }
  }	else if (value&&value!=Math.round(value*100)/100){
 		document.getElementById(fields[e]).value=Math.round(value*100)/100;
 	}
 }
}

function format(s) {
s = new String(s);
s = s.replace( /(?=([0-9]{3})+$)/g, " " );
return s;
}


function  recount(e,event)
{
	if (!event) event=window.event;
 	if (e)
  {
    if (e.value != e.value.replace(/[^0-9.]/g, "")) e.value = e.value.replace(/[^0-9.]/g, "");
    if (event.keyCode==9) return;
    if (e.id in recFields)
    	{
     	if (e.value)
      	{
  	   	lastButOne=last;
    	 	last=e.id;
       	}
      else
      	{
       	last=lastButOne;
        lastButOne="";
        }

      }
    if (e.id=="month") {
   		if (e.value>11) e.value=11;
   	}
  }

	var period=parseInt(year.value)*12+(isNaN(parseInt(month.value))?0:parseInt(month.value));
 	if (period||last==permonth) {
    switch (last)	{
      case "first":
      case "init": //При загрузке
      case "cost":
      	sum.value=cost.value-first.value;
      case "year":
      case "month":
      case "percent":
      case "sum":
       	var perc=percent.value/1200;
  	    permonth.value=sum.value*(perc + perc/(Math.pow(1+perc,period)-1));
      break;

      case "permonth":
      	var perc=percent.value/1200;
       	if (permonth.value>0) {
       		period=Math.log(permonth.value/(permonth.value-sum.value*perc))/Math.log(1+perc);
        } else {
        	period=0;
        }
        month.value=Math.round(period%12);
        year.value=Math.floor(period/12);
        if (month.value==12) {
  	      month.value=0;
        	year.value++;
        }
      break;
    }
  }
  if (last) round();
  calcYear();
}

function calcYear() {
	var y="";
	last=year.value.substring(year.value.length-1,year.value.length);
 	preLast=year.value.substring(year.value.length-2,year.value.length-1);
  if (!preLast) preLast=0;
  if (preLast==1) {
  	y="лет";
  } else {
    if (last==1) {
    	y="год";
    } else if (last>1&&last<=4) {
    	y="года";
    } else {
    	y="лет";
    }
  }
  document.getElementById("yearL").innerHTML=y;
}

</script>
<title>Контакт - Ипотечный калькулятор</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link href="/css/base.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF" style="padding:10px">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
   <tr>
   <td width="100%" valign="top" colspan="4" class="base">
   <table class=calc>
   <tr><td colspan=3><h2>Ипотечный калькулятор</h2></td></tr>
   <tr><td>Полная cтоимость квартиры, $:</td><td width=21></td><td colspan=2><input type="text" id="cost"  onKeyUp="recount(this,event)" class="calc_form"></td></tr>
    <tr vAlign=top><td>Первоначальный взнос, $:</td><td width=21></td><td colspan=2><input type="text" id="first" onKeyUp="recount(this,event)" class="calc_form"></td></tr>
    <tr><td>Сумма кредита, $:</td><td width=21></td><td colspan=2><input type="text" id="sum" onKeyUp="recount(this,event)" class="calc_form"></td></tr>
    <tr><td>Срок кредита:</td><td width=21></td><td width=125><input type="text" id="year" onKeyUp="recount(this,event)" style="width:90px;"> <span id="yearL">лет</span></td><td width=125><input style="width:80px;" type="text" id="month" onKeyUp="recount(this,event)"> мес.</td></tr>
    <tr><td>Процентная ставка, %:</td><td width=21></td><td colspan=2><input type="text" id="percent" onKeyUp="recount(this,event)" class="calc_form"></td></tr>
    <tr><td>Ежемесячный платеж, $:</td><td width=21></td><td colspan=2><input type="text" id="permonth" onKeyUp="recount(this,event)" class="calc_form"></td></tr>
   </table>
   <script>init()</script>
   <small><P>Дополнительные расходы: банковский сбор, расходы по страхованию, расходы по оформлению кредита и другие. Их величина меняется в зависимости от выбранной кредитной схемы. По этим вопросам всегда можно проконсультироваться с нашим представителем.</P>
<P>ВНИМАНИЕ: здесь представлен расчет усредненных данных по ипотечному кредиту. Мы можем предложить и другие программы в зависимости от Ваших индивидуальных условий, возможностей и потребностей. Для этого необходимо проконсультироваться с нашим представителем.</P></small>
   </td>
   </tr>
   </table>
</body>
</html>
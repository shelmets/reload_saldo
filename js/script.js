const monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
"Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
];
 //чистим внутренность тега table 
function addDelTable(){
	$('table').text("");
	let ths = $('table').children();
	for(var i=0,l=ths.length;i<l;i++){
		ths[i].remove();
	};
	/*switch($('#action')[0].value){
		case "charges":
		case "payments":
			$('<th scope="col">#</th><th scope="col">Дата</th><th scope="col">Квартира</th><th scope="col">Сумма</th><th scope="col">Функция</th>')
			.appendTo('#trTable');
			break;
		case "saldo":
			$('<th scope="col">#</th><th scope="col">Дата</th><th scope="col">Квартира</th><th scope="col">Начисления</th><th scope="col">Платежи</th><th scope="col">Функция</th>')
			.appendTo('#trTable');
			break;
		case "sheet1":
			$('');
			break;
		case "sheet2":
			;
			break;
		default:
			break;
		};*/

	};

	function display(){
		var secondGroupButtons = $('#secondGroupButtons')[0];
		addDelTable();
		$('#filters')[0].style.display = "none";
		$("#add_delete")[0].style.display = "none";
		switch ($('#action')[0].value){
			case "payments":
			case "charges":
			case "saldo":
				var responceRangeFlats = sendAjaxRequestRangeFlats("scripts/handler.php", $('#action')[0].value);
				$("#from").val((responceRangeFlats['min']!=null)?responceRangeFlats['min']:0);
				$("#to").val((responceRangeFlats['max']!=null)?responceRangeFlats['max']:0);
				secondGroupButtons.style.display = "block";
				if ($('#gridRadios1').is(':checked')){
					changeInputYearMonth("month");
				} else{
					changeInputYearMonth("year");}
			break;
			case "sheet1":
			case "sheet2":
				secondGroupButtons.style.display = "none";
			break;
			case "default":
			break;
		}
	}
	function handlerPayment(result, result_form){
		var str = '<thead><tr id = "trTable"><th score="col">id</th><th score="col">Квартира</th><th score="col">Дата</th><th score="col">Сумма</th><th score="col">Функция</th></tr></thead>',
		body = "";
		for (var i = 0, l = result["id"].length;i < l;i++){
			body+=`<tr><th score="col">${result["id"][`${i}`]}</th><th score="col">${result["flat"][`${i}`]}</th><th score="col">${result["date"][`${i}`]}</th><th score="col">${result["cash"][`${i}`]}</th></tr>`;
		};
		str+=`<tbody>${body}</tbody>`;
		$('#'+result_form).html(str);	
	}
	function handlerCharge(result, result_form){
		var str = '<thead><tr id = "trTable"><th score="col">id</th><th score="col">Квартира</th><th score="col">Дата</th><th score="col">Сумма</th><th score="col">Функция</th></tr></thead>',
		body = "";
		for (var i = 0, l = result["id"].length;i < l;i++){
			body+=`<tr><th score="col">${result["id"][`${i}`]}</th><th score="col">${result["flat"][`${i}`]}</th><th score="col">${result["date"][`${i}`]}</th><th score="col">${result["cash"][`${i}`]}</th></tr>`;
		};
		str+=`<tbody>${body}</tbody>`;
		$('#'+result_form).html(str);
	}
	function handlerSaldo(result, result_form){
		var str = '<thead><tr id = "trTable"><th score="col">id</th><th score="col">Квартира</th><th score="col">Дата</th><th score="col">Платеж</th><th score = "col">Начисления</th><th score="col">Функция</th></tr></thead>',
		body = "";
		for (var i = 0, l = result["id"].length;i < l;i++){
			body+=`<tr><th scope="col">${result["id"][`${i}`]}</th><th score="col">${result["flat"][`${i}`]}</th><th score="col">${result["date"][`${i}`]}</th><th score="col">${result["payment"][`${i}`]}</th><th score = "col">${result["charge"][`${i}`]}</tr>`;
		};
		str+=`<tbody>${body}</tbody>`;
		$('#'+result_form).html(str);
	}
	function handlerSheet1(result, result_form){
		var str = '<thead><tr id = "trTable"><th score="col">Квартира</th><th score="col">Начальное сальдо</th>',
		body = "";
		for (var i = 1; i < 13; i++){
			str+=`<th score = "col">${i}</th>`;
		};
		str+="</tr></thead>";
		for (var i = 0, l = result["Flats"].length;i < l;i++){
			body+=`<tr><th scope="col">${result["Flat"][`${i}`]}</th><th score="col">${result["Saldo_begin"][`${i}`]}</th>`;
			for (var j = 0; j < 13;j++){
				body+=`<th score = "col">${result[`${j}`][`${i}`]}</th>`;
			}
			body+="</tr>";
		}
		str+=`<tbody>${body}</tbody>`;
		$('#'+result_form).html(str);
	}
	function handlerSheet2(result, result_form){
		var str = '<thead><th score="col">Начальное сальдо</th><th score = "col"></th>';
		for (var i = 1; i < 13; i++){
			str+=`<th score = "col">${i}</th>`;
		};
		str+='<th score = "col">Итог</th><th score = "col">Конечное сальдо</th>/tr></thead>';
		var body = `<tr><th score = "col" rowspan = "2">${result["Saldo_begin"]}</th><th score = "col">Платежи</th>`;
		for (var j = 0; j < 13;j++){
			body+=`<th score = "col">${result[`${j}`]["payment"]}</th>`;
		}
		body+="</tr>";
		body = `<tr><th score = "col">Начисления</th>`;
		for (var j = 0; j < 13;j++){
			body+=`<th score = "col">${result[`${j}`]["charge"]}</th>`;
		}
		body+="</tr>";
		str+=`<tbody>${body}</tbody>`;
		$('#'+result_form).html(str);
	}
	function handlerServerResult(result, result_form)
	{
		if ("error" in result){
			$('#'+result_form).html(`Error php. ${result[`error`]}`);
		}
		else{
			if ("action" in result){
				switch(result["action"]){
					case "payments":
						handlerPayment(result, result_form);
						break;
					case "charges":
						handlerCharge(result, result_form);
						break;
					case "saldo":
						handlerSaldo(result, result_form);
						break;
					case "sheet1":
						handlerSheet1(result, result_form);
						break;
					case "sheet2":
						handlerSheet2(result, result_form);
						break;
				}
			}
			else{
				$('#'+result_form).html("Error. Response do not have key action.");
			}
		}

	}
	function changeInputYearMonth(str)
	{
		if (str == "year"){
			$('#labelInput').text("Год:");
			$('#inputYearMonth').attr("type", "number");
			$('#inputYearMonth').attr("style", "width:30%");
			$('#inputYearMonth').attr("value", "2018");
			$('#inputYearMonth').attr("min", "0");
			$('#inputMonth').text("Расчетный год ____");
		} else if (str == "month") {
			$('#labelInput').text("Месяц:");
			$('#inputYearMonth').attr("type", "month");
			$('#inputYearMonth').attr("style", "width:70%");
			$('#inputYearMonth').attr("value", "2018-06");
			$('#inputMonth').text("Расчетный год ____ месяц ____");
		}
	}
	function sendAjaxFormShow(result_form, ajax_form, url, type){
		$.ajax({
			url: url,
			type: type,
			dataType: "html",
			data: $("#"+ajax_form).serialize(),
			success: function(response){
				var result = $.parseJSON(response);
				handlerServerResult(result, result_form);
			},
			error: function(response){
				$('#'+result_form).html("Error. Data don't sent!")
			}
		});
	}
	function sendAjaxRequestRangeFlats(url, table){
		$.ajax({
			url: url,
			type: "GET",
			data: {action: "rangeFlats", table: table},
			success: function(response){
				return $.parseJSON(response);
			},
			error: function(response){
				return null;
			}
		});
	}
	$(document).ready(function(){
		$('#filters')[0].style.display = "none";
		$("#add_delete")[0].style.display = "none";
		$("#action")[0].style.display = "none";
		$('#chargesLink').on("click",function(){
			$('#action').val("charges");
			display("block");
			$('#headline').text("Начисления");
		});
		$('#paymentsLink').click(function(){
			$('#action').val("payments");
			display();
			$('#headline').text("Платежи");
		});
		$('#saldoLink').click(function(){
			$('#action').val("saldo");
			display();
			$('#headline').text("Начальное сальдо");
		});
		$('#table1').click(function(){
			$('#action').val("sheet1");
			display();
			$('#headline').text("Оборотная ведомость 1");
			changeInputYearMonth("year");
		});
		$('#table2').click(function(){
			$('#action').val("sheet2");
			display();
			$('#headline').text("Оборотная ведомость 2");
			changeInputYearMonth("month");
		});
		$('#filterButton').click(function(){
			var element = $('#filters')[0],
			dynamicFilters = $('#dynamicDisplay')[0],
			action = $('#action')[0];
			if (element.style.display == "none"){
				element.style.display = "block";
			if (action.value=="sheet1" || action.value=="sheet2")
				{dynamicFilters.style.display = "none";}
			else
				{dynamicFilters.style.display = "block";}
			}
			else
			{element.style.display = "none"}

	});
		$('#show').click(function(){
			addDelTable();
			sendAjaxFormShow("table","ajax_form1", "scripts/handler.php", "GET");
			return false;
		});
		$('#add').click(function(){
			add_delete = $("#add_delete")[0];
			if (add_delete.style.display == "none")
				{add_delete.style.display = "block"}
			else
				{add_delete.style.display = "none"}
		});
		$('#delete').click(function(){
			add_delete = $("#add_delete")[0];
			if (add_delete.style.display == "none")
				{add_delete.style.display = "block"}
			else
				{add_delete.style.display = "none"}
		});
	// обработка события изменения чекбокса "Видов отображения"
	$('#gridRadios1').click(function(event){  
		changeInputYearMonth("month");
	});
	$('#gridRadios2').click(function(event){
		changeInputYearMonth("year");
	});
	//применяем фильтры
	$('#apply').click(function(){
		var mass = $('#inputYearMonth')[0].value.split('-');
		if (mass.length>1){
			$('#inputMonth').text(`Расчетный год ${mass[0]} месяц ${monthNames[Number(mass[1])]}`);
		}
		else{
			$('#inputMonth').text(`Расчетный год ${mass[0]}`);
		}
	});
});

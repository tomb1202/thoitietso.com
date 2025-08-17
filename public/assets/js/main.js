(function ($) {
	$(document).ready(function () {
		// handle scroll weather
		$(".weather-time-actions .arrow-left").click(function () {
			$(".weather-time-list").animate(
				{
					scrollLeft: "-=160px",
				},
				"slow"
			);
		});
		$(".weather-time-actions .arrow-right").click(function () {
			$(".weather-time-list").animate(
				{
					scrollLeft: "+=160px",
				},
				"slow"
			);
		});

		// init calendar
		var calendar;
		if ($("#calendar-container").length) {
			console.log("Hello");
			let container = $("#calendar-container").simpleCalendar({
				fixedStartDay: 1,
				disableEmptyDetails: true,
				events: [],
			});
			calendar = container.data("plugin_simpleCalendar");
		}

		// more locations homepage
		if ($(".weather-city-inner").length) {
			var location = $(".weather-city-inner");
			if (location.find("li").length > 24) {
				$(".items").append(
					'<button class="button-primary showMore"><a href="javascript:void(0)">Xem thêm</a></button>'
				);
			}

			$(".weather-city-inner li").slice(0, 24).addClass("shown");
			$(".weather-city-inner li").not(".shown").hide();
			$(".weather-city-inner .showMore").on("click", function () {
				$(".weather-city-inner li").not(".shown").toggle();
				$(this).toggleClass("showLess");
			});
		}

		// more hourly weathers
		// if ($(".hourly-weather").length) {
		// 	$(".hourly-weather .weather-feature-item")
		// 		.slice(0, 6)
		// 		.addClass("shown");
		// 	$(".hourly-weather .weather-feature-item").not(".shown").hide();
		// 	$(".weather-feature-btns .showMore").on("click", function () {
		// 		$(".hourly-weather .weather-feature-item")
		// 			.not(".shown")
		// 			.toggle();
		// 		$(this).toggleClass("showLess");
		// 	});
		// }

		// handle search location
		$(".search-input").keyup(
			delay(function () {
				$(".search-results").fadeIn();
				$.ajax({
					type: "GET",
					dataType: "html",
					url: ajaxUrl,
					data: {
						action: "search_location",
						keyword: this.value,
					},
					beforeSend: function () {
						$(".search-results").html(
							"<div class='loadingspinner'></div>"
						);
					},
					success: function (response) {
						$(".search-results").html(response);
					},
					error: function (jqXHR, textStatus, errorThrown) {
						console.log(
							"The following error occured: " + textStatus,
							errorThrown
						);
					},
				});
			}, 500)
		);
		$(document).mouseup(function (e) {
			var searchField = $(".search-input");
			if (
				!searchField.is(e.target) &&
				searchField.has(e.target).length === 0
			) {
				searchField.val("");
				$(".search-results").fadeOut();
			}
		});

		//widget page
		$(".widget-search-location").keyup(
			delay(function () {
				$(".widget-search-results").fadeIn();
				$.ajax({
					type: "GET",
					dataType: "html",
					url: ajaxUrl,
					data: {
						action: "widget_search_location",
						keyword: this.value,
					},
					beforeSend: function () {
						$(".widget-search-results").html(
							"<div class='loadingspinner'></div>"
						);
					},
					success: function (response) {
						$(".widget-search-results").html(response);
					},
					error: function (jqXHR, textStatus, errorThrown) {
						console.log(
							"The following error occured: " + textStatus,
							errorThrown
						);
					},
				});
			}, 500)
		);
		$(document).mouseup(function (e) {
			var searchField = $(".widget-search-location");
			if (
				!searchField.is(e.target) &&
				searchField.has(e.target).length === 0
			) {
				$(".widget-search-results").fadeOut();
			}
		});
		$(document).on("click", ".widget_term_id", function () {
			let termId = $(this).data("href");
			let value = $(this).text();
			$("#widget_term_id").val(termId);
			$(".widget-search-location").val(value);
		});
		$("#formControlRange").change(function () {
			$("#range").html(this.value + "px");
			$(".widget-container").css("width", this.value + "px");
		});

		$(".create-widget").submit(function (e) {
			e.preventDefault();
			let formData = $(this).serialize();

			$.ajax({
				type: "GET",
				dataType: "html",
				url: ajaxUrl,
				data: {
					action: "create_widget",
					data: formData,
				},
				success: function (response) {
					$(".widget-container").html(response);
					$("#urlValue").val(response);
				},
			});
		});

		$(".btn-copy").click(function () {
			$("#urlValue").select();
			document.execCommand("copy");
			alert("Đã copy thành công!");
		});

		$("#tracuu").submit(function (event) {
			event.preventDefault();
			var sbd = $("#tracuu input").val();
			$.ajax({
				type: "POST",
				url: "https://diemthi.tuoitre.vn/search-thpt-score",
				data: {
					data: sbd,
					code: "",
				},
				success: function (data) {
					var myArray = data.score.split(";");
					var html =
						'<table class="table tbl-score"><tbody><tr><td scope="col">Môn học</td><td scope="col">Số điểm</td></tr>';
					myArray.forEach((element) => {
						var item = element.split(":");
						if (item[0]) {
							html += "<tr>";
							html +=
								item[0] == "NgoạiNgữ"
									? "<td>Ngoại ngữ</td>"
									: item[0] == "Mãmônngoạingữ"
									? "<td>Mã môn ngoại ngữ</td>"
									: "<td>" + item[0] + "</td>";
							html += "<td>" + item[1] + "</td>";
							html += "</tr>";
						}
					});
					html += "</tbody></table>";
					$(".show-ket-qua").html(html);
				},
			});
		});

		$("#showMoreContent").click(function (evt) {
			evt.preventDefault();
			let parent = $(this)
				.closest(".collapse-desc")
				.find(".entry-content");
			if (parent.hasClass("active")) {
				parent.removeClass("active");
				$("#showMoreContent").text("Mở rộng");
			} else {
				parent.addClass("active");
				$("#showMoreContent").text("Thu gọn");
			}
		});
	});
})(jQuery);

function delay(callback, ms) {
	var timer = 0;
	return function () {
		var context = this,
			args = arguments;
		clearTimeout(timer);
		timer = setTimeout(function () {
			callback.apply(context, args);
		}, ms || 0);
	};
}

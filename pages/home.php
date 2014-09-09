<div class="content-wrapper">
    <div class="main-content">
    </div>
    <div class="right-side">
        <div class="widget">
            <div class="title">
                Upcoming Events
            </div>
            <ul class="events top">
            </ul>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<script>
    var initialize = function() {
        $LAB
            .script("//connect.facebook.net/en_US/sdk.js").wait()
            .script("./js/fbinit.js")
            .script("./js/isotope.pkgd.min.js").wait()
            .script("./js/albumviewer.js").wait(function() {
                getPosts(nextPage);
            })
            .script("//www.datejs.com/build/date.js")
            .script("//apis.google.com/js/client.js?onload=makeEvents").wait();
    }

    var nextPage = 0;
    var posts = [
        {
            title: "Family Camp 2014 (July 27-29)",
            albumId: 409578535847055,
            description: "Bacon ipsum dolor sit amet filet mignon short loin t-bone hamburger. Tenderloin shank kielbasa jerky andouille drumstick spare ribs bacon hamburger cow tri-tip jowl biltong t-bone. Capicola prosciutto shoulder landjaeger bresaola shankle corned beef leberkas. Ground round andouille pancetta salami meatball. Ham pork chop flank corned beef, turkey shoulder t-bone rump doner sausage. Flank jowl turkey bresaola, turducken pork frankfurter tongue cow. Tail tenderloin doner, salami pig drumstick jerky corned beef meatloaf."
        },
        {
            title: "Swimming at Eileen Daily Pool (July 21)",
            albumId: 407587682712807,
            description: "Bacon ipsum dolor sit amet filet mignon short loin t-bone hamburger. Tenderloin shank kielbasa jerky andouille drumstick spare ribs bacon hamburger cow tri-tip jowl biltong t-bone. Capicola prosciutto shoulder landjaeger bresaola shankle corned beef leberkas. Ground round andouille pancetta salami meatball. Ham pork chop flank corned beef, turkey shoulder t-bone rump doner sausage. Flank jowl turkey bresaola, turducken pork frankfurter tongue cow. Tail tenderloin doner, salami pig drumstick jerky corned beef meatloaf."
        }
    ];

    var getPosts = function(page, limit) {
	    if(page == null) return;
	    $.ajax({
		    url: "json.php",
		    dataType: "json",
		    method: "post",
		    data: {
				page: page,
			    limit: limit
		    }
	    }).done(function(response) {
            console.log(response);
		    posts = response.data;

		    if(typeof response.nextPage != "undefined")
		        nextPage = null;
		    else
			    nextPage = response.nextPage;

		    genPosts();
	    });
    };

    var genPosts = function() {
	    var count = (typeof posts.nextPage == "undefined" ? posts.length : posts.length - 1);
        for(var i = 0; i < count; i++) {
            posts[i].elem = $('<div class="post">' +
                    '<div class="title">' +
                    posts[i].title +
                    '</div>' +
                    '<div class="photo loading" onclick="fbAlbumInit(' + posts[i].albumId + ');"></div>' +
                    '<div class="words">' +
                    cropWords(posts[i].description, 40) + "..." +
                    '</div>' +
                    '<a href="javascript:void(0);" class="read-more"></a>' +
                    '</div>');
            $(".main-content").append(posts[i].elem);

            FB.api(
		            "/" + posts[i].albumId,
                    'get',
                    {
                        pretty: 0,
                        access_token: ACCESS_TOKEN
                    },
                    function ( response ) {
                        var coverElem = posts[getPostByAlbumId(response.id)].elem.find(".photo");
                        if (response && !response.error) {
                            var img = $('<img/>');
                            getPhotoData(response.cover_photo, function( response ) {
                                if (response && !response.error) {
                                    img[0].src = response.images[4].source;
                                    img.load(function () {
                                        coverElem.css('background-image', "url(" + response.images[4].source + ")");
                                        coverElem.animate({
                                            height: "332px"
                                        }, 1000, function () {
                                            coverElem.css("height", "");
                                        });
                                        coverElem.removeClass("loading");
                                    });
                                }
                            });
                        }
                    }
            );
        }
    };

    var getPhotoData = function( photoId, callback ) {
        FB.api("/" + photoId, 'get', {pretty: 0, access_token: ACCESS_TOKEN}, callback);
    };

    var getPostByAlbumId = function( albumId ) {
        for(var i = 0; i < posts.length; i++) {
            if(posts[i].albumId == albumId)
                return i;
        }
        return -1;
    };

    var makeEvents = function() {
        var today = new Date().clearTime();
        gapi.client.setApiKey('AIzaSyCvuJzS-Q7uGdliRFqySq0mYar0YOBQEGE');
        gapi.client.load('calendar', 'v3', function() {
            var request = gapi.client.calendar.events.list({
                calendarId: 'pccrovers.com_pojeic2sd1ojijt7ohop7gt338@group.calendar.google.com',
                orderBy: 'startTime',
                singleEvents: true,
                timeMin: today.toISOString(),
                timeMax: today.moveToDayOfWeek(0).addWeeks(3).toISOString(),
                timeZone: 'America/Vancouver',
                fields: 'items(summary,description,start,end,endTimeUnspecified,location,htmlLink,updated, description)'
            });
            request.execute(function(response) {
                console.log(response);
                if (response && !response.error) {
                    var dates = {};
                    for(var i = 0; i < response.items.length; i++) {
                        var item = response.items[i];
                        var start = Date.parse(item.start.dateTime ? item.start.dateTime : item.start.date);

                        if(!dates[start.clearTime().toString("MMM d")]) dates[start.clearTime().toString("MMM d")] = [];

                        dates[start.clearTime().toString("MMM d")].push(item);
                    }

                    for(var date in dates) {
                        var $dateLabel = $("<a href=\"javascript: void(0);\" class=\"summary day\" onclick=\"$(this).next().slideToggle(200);$(this).toggleClass('open');\">" + date + "</a>");
                        var elem = "<li class=\"item\"><div style=\"display:none;\"><ul class=\"events\">";

                        dates[date].sort(function(a, b){
                            var rowA = a.summary.toLowerCase(), rowB = b.summary.toLowerCase();
                            if (rowA < rowB)
                                return -1;
                            if (rowA > rowB)
                                return 1;
                            return 0;
                        })

                        for(var index in dates[date]) {
                            var event = dates[date][index];

                            if(event.summary.substring(0, 2) == "GC") {
                                $dateLabel.append(event.summary.substring(2));
                                continue;
                            }

                            var useDateTime = (event.start.dateTime ? true : false);
                            var startTime = Date.parse(useDateTime ? event.start.dateTime : event.start.date);
                            var endTime = Date.parse(useDateTime ? event.end.dateTime : event.end.date);
                            var isMultiDay = (Date.parse(startTime.toString('M/d/yyyy')).compareTo(Date.parse(endTime.toString('M/d/yyyy'))) == -1);

                            elem += '<li class="item">' +
                                '<a href="javascript: void(0);" class="summary" onclick="$(this).next().slideToggle(200);$(this).toggleClass(\'open\');">' +
                                event.summary +
                                '</a>' +
                                '<div style="display:none;">' +
                                '<b>Date:</b> ' + start.toString('dddd MMMM d, yyyy') + '<br/>' +
                                '<b>Time:</b> ' + (useDateTime ? startTime.toString((isMultiDay ? 'ddd ' : '') + 'h:mmtt') + ' &ndash; ' + endTime.toString((isMultiDay ? 'ddd ' : '') + 'h:mmtt') : 'All Day') + '<br/>' +
                                (event.location ? '<b>Location:</b> <a href="//maps.google.ca/maps?hl=en&q=' + event.location + '&source=calendar">' + event.location + '</a><br/>' : '') +
                                '<span onclick="$(this).toggleClass(\'open\')"><b>Description:</b> ' + event.description + '</span><br/>' +
                                '</div>' +
                                '</li>';
                        }

                        elem += "</ul></div></li>";
                        var $elem = $(elem).prepend($dateLabel);
                        $("ul.events.top").append($elem);
                    }
                }
            });
        });
    };

    var cropWords = function( str, count ) {
        return str.replace(/(<([^>]+)>)/ig,"").split(/\s+/, count).join(" ");
    };

</script>
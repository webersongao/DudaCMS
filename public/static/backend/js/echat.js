function getDate() {
    var date = new Date(),
        Year = 0,
        beforeYear = 0,
        Month = 0,
        beforeSixMonth = 0,
        Day = 0,
        startDate = "",
        endDate = "",
        startDateM = "",
        endDateM = "",
        arr = [];

    Year = date.getFullYear();
    Month = date.getMonth() + 1;
    Day = date.getDate();
    if (Month > 6) {
        beforeSixMonth = Month - 6;
        beforeYear = date.getFullYear();
    } else {
        beforeYear = date.getFullYear() - 1;
        beforeSixMonth = 6 + Month;
    }
    startDate += Year + "-";
    endDate += beforeYear + "-";
    startDate += Month >= 10 ? Month + "-" : "0" + Month + "-";
    endDate += beforeSixMonth >= 10 ? beforeSixMonth + "-" : "0" + beforeSixMonth + "-";
    startDate += Day >= 10 ? Day : "0" + Day;
    endDate += Day >= 10 ? Day : "0" + Day;
    startDateM = startDate;
    endDateM = endDate;
    arr.push(startDate, endDate, startDateM, endDateM);
    return arr;
}

function Echarts(element, type, url, options, onLoaded) {
    this.element = element;
    this.type = type;
    this.url = url;
    this.chart = null;
    this.onLoaded = typeof onLoaded === 'function' ? onLoaded : null;
    this.retried = false;
    this.requestSeq = 0;
    this.retryTimer = null;
    this.options = {
        color: ['#056DE8', '#00A870', '#F5A623', '#E34D59', '#722ED1', '#13C2C2'],
        animation: false,
        grid: {
            left: 16,
            right: 24,
            top: 56,
            bottom: 16,
            containLabel: true,
            backgroundColor: 'transparent',
            borderColor: 'transparent'
        },
        legend: {
            data: [],
            top: 8,
            left: 12,
            right: 12,
            itemWidth: 14,
            itemHeight: 8,
            itemGap: 18,
            selectedMode: false,
            textStyle: { color: '#4E5969', fontSize: 12 }
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'line', lineStyle: { color: '#C9CDD4' } },
            backgroundColor: 'rgba(255,255,255,.97)',
            borderColor: '#E5E6EB',
            textStyle: { color: '#1D2129', fontSize: 12 }
        },
        xAxis: [{
            type: 'category',
            boundaryGap: false,
            axisLine: { lineStyle: { color: '#E5E6EB' } },
            axisTick: { show: false },
            axisLabel: {
                color: '#86909C',
                fontSize: 11,
                margin: 12,
                interval: 'auto',
                hideOverlap: true
            },
            data: []
        }],
        yAxis: [{
            type: 'value',
            minInterval: 1,
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: { color: '#86909C', fontSize: 11, margin: 12 },
            splitLine: { show: true, lineStyle: { color: '#EFF1F5', type: 'dashed', width: 1 } }
        }]
    };

    this.options = $.extend(true, {}, this.options, options || {});
    this.initChart(url);
}

Echarts.prototype = {
    initChart: function (url) {
        if (url) {
            this.url = url;
        }
        if (this.retryTimer) {
            clearTimeout(this.retryTimer);
            this.retryTimer = null;
        }
        this.retried = false;
        this.getData(this.url, ++this.requestSeq);
    },

    initXAxis: function (data) {
        return {
            xAxis: [{
                type: 'category',
                boundaryGap: false,
                axisLine: { lineStyle: { color: '#E5E6EB' } },
                axisTick: { show: false },
                axisLabel: {
                    color: '#86909C',
                    fontSize: 11,
                    margin: 12,
                    interval: 'auto',
                    hideOverlap: true
                },
                data: data
            }]
        };
    },

    getData: function (url, seq) {
        var _this = this;
        if (!url) return;
        if (typeof seq !== 'number') {
            seq = ++this.requestSeq;
        }
        $.ajax({
            url: url,
            type: 'POST',
            data: { async: true },
            dataType: 'json',
            global: false,
            success: function (result) {
                if (seq !== _this.requestSeq) return;
                if (!result || !result.code || !result.data) {
                    _this.finish(seq);
                    return;
                }
                var xAxis = _this.initXAxis(result.data.labels || []);
                var legend = _this.getUrlParam('tag');
                var series = _this.initSeries(_this.type, result.data.data || []);
                _this.options = $.extend(true, {}, _this.options, xAxis, series, legend);
                _this.render();
                _this.finish(seq);
            },
            error: function () {
                if (seq !== _this.requestSeq) return;
                // 后台统计属于辅助数据，偶发连接抖动时重试一次即可，不打断用户操作。
                if (_this.retried) {
                    _this.finish(seq);
                    return;
                }
                _this.retried = true;
                _this.retryTimer = setTimeout(function () {
                    _this.retryTimer = null;
                    _this.getData(url, seq);
                }, 400);
            }
        });
    },

    finish: function (seq) {
        if (seq !== this.requestSeq) return;
        if (this.onLoaded) {
            this.onLoaded(this);
        }
    },

    initSeries: function (type, data) {
        if (!data) return { series: [] };
        var arr = [];
        for (var i = 0; i < data.length; i++) {
            arr.push({
                name: (this.legend_data && this.legend_data[i]) || ('系列' + (i + 1)),
                type: type,
                smooth: true,
                showSymbol: false,
                symbol: 'circle',
                symbolSize: 6,
                lineStyle: { width: 2 },
                emphasis: { focus: 'series' },
                data: data[i]
            });
        }
        return { series: arr };
    },

    render: function () {
        var element = $(this.element)[0];
        if (!element) return;
        if (!this.chart) {
            this.chart = echarts.init(element);
        }
        this.chart.setOption(this.options, true);
    },

    resize: function () {
        if (this.chart) {
            this.chart.resize();
        }
    },

    getUrlParam: function (name) {
        var start = this.url.indexOf(name);
        if (start === -1) return { legend: { data: [] } };
        var param = this.url.substring(start + name.length + 1).split('&')[0].split(',');
        var arr = [];
        var labels = {
            new_answer: '新增答案',
            new_question: '新增问题',
            new_user: '新注册用户',
            user_valid: '新激活用户',
            new_topic: '新增话题',
            new_answer_vote: '新增回复投票',
            new_answer_thanks: '新增回复感谢',
            new_favorite_item: '新增收藏',
            new_question_thanks: '新增问题感谢',
            new_question_redirect: '新增问题重定向',
            new_article: '新增文章'
        };
        for (var i = 0; i < param.length; i++) {
            if (labels[param[i]]) {
                arr.push(labels[param[i]]);
            }
        }
        this.legend_data = arr;
        return {
            legend: {
                data: arr,
                top: 8,
                left: 12,
                right: 12,
                itemWidth: 14,
                itemHeight: 8,
                itemGap: 18,
                selectedMode: false,
                textStyle: { color: '#4E5969', fontSize: 12 }
            }
        };
    }
};

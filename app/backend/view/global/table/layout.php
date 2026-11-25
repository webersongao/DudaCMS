{extend name="block" /}
{block name="main"}
{php}
// bootstrap-table 仅列表页加载；未使用的 fixed-columns/toolbar/treegrid/multiple-sort 扩展已移除。
$assetRoot = $assetBase ?? '/static/';
echo '<link rel="stylesheet" href="' . $assetRoot . 'vendor/bootstrap-table/bootstrap-table.min.css?v=' . asset_version('vendor/bootstrap-table/bootstrap-table.min.css') . '">';
echo '<script src="' . $assetRoot . 'vendor/bootstrap-table/bootstrap-table.min.js?v=' . asset_version('vendor/bootstrap-table/bootstrap-table.min.js') . '"></script>';
echo '<script src="' . $assetRoot . 'vendor/bootstrap-table/locale/bootstrap-table-zh-CN.min.js?v=' . asset_version('vendor/bootstrap-table/locale/bootstrap-table-zh-CN.min.js') . '"></script>';
echo '<script src="' . $assetRoot . 'vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.js?v=' . asset_version('vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.js') . '"></script>';
echo '<script src="' . $assetRoot . 'vendor/bootstrap-table/extensions/export/tableExport.js?v=' . asset_version('vendor/bootstrap-table/extensions/export/tableExport.js') . '"></script>';
echo '<script src="' . $assetRoot . 'vendor/bootstrap-table/extensions/export/bootstrap-table-export.js?v=' . asset_version('vendor/bootstrap-table/extensions/export/bootstrap-table-export.js') . '"></script>';
{/php}
<section class="p-3">
    <!--额外CSS代码-->
    {$extra_css|raw|default=''}
    <!--额外HTML代码-->
    {$extra_html_top|raw|default=''}
    <!--顶部提示开始-->
    {if $page_tips_top}
    <div class="alert alert-{$tips_type} alert-dismissable">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <p>{$page_tips_top|raw}</p>
    </div>
    {/if}
    <!--顶部提示结束-->
    <div class="container-fluid">
        <div class="row">
            {if !empty($group)}
            <div class="w-100 bg-white mb-2">
                <ul class="nav nav-tabs nav-tabs-alt js-tabs-enabled">
                    {volist name="group" id="item" key="items_key"}
                    <li class="nav-item">
                        <a class="nav-link {if $item.active}active{/if}" href="{$item.link}" data-pjax="wrapMain">{:L($item.title)}</a>
                    </li>
                    {/volist}
                </ul>
            </div>
            {/if}
            <!--搜索区域开始-->
            {if $search OR $page_tips_search }
            <div class="col-12 search-collapse">
                <fieldset>
                    <legend>{:L('条件选项')}</legend>
                    <form id="search_form" class="mb-0">
                        <div class="select-list">
                            {notempty name="page_tips_search"}{$page_tips_search|raw}{/notempty}
                            {notempty name="search"}
                            <ul class="clearfix mb-0">
                                {volist name="search" id="search"}
                                <li>
                                    <label>{$search.title|default=''}： </label>
                                    {if $search.param }
                                    {if $search.type == 'text' || $search.type == 'textarea' || $search.type == 'number' || $search.type == 'hidden'}
                                    <input type="text" id="search_{$search.name|default=''}" name="{$search.name|default=''}" value="{$search.default|default=''}"/>
                                    {else}
                                    <select id="search_{$search.name|default=''}"  name="{$search.name|default=''}">
                                        <option value="">{:L('所有')}</option>
                                        {notempty name="search.param"}
                                        {volist name="search.param" id="v"}
                                        <option value="{$key}" {if ((string)$search.default == (string)$key)}selected{/if}>{$v}</option>
                                        {/volist}
                                        {/notempty}
                                    </select>
                                    {/if}
                                    {else}
                                    {if $search.type == 'date' OR $search.type == 'time' OR $search.type == 'datetime' }
                                    {// 日期类型的数据 }
                                    <input type="text" id="search_{$search.name|default=''}" name="{$search.name|default=''}" value="{$search.default|default=''}" autocomplete="off" data-dask-field="laydate" data-lay-type="datetime" data-lay-range="至"/>
                                    {elseif $search.type == 'select2'}
                                    <select class="select2" id="search_{$search.name|default=''}" name="{$search.name|default=''}" data-value="{$search.default|default=''}">
                                        <option value="">{:L('所有')}</option>
                                        {notempty name="search.param"}
                                        {volist name="search.param" id="v"}
                                        <option value="{$key}" {if ((string)$search.default == (string)$key)}selected{/if}>{$v}</option>
                                        {/volist}
                                        {/notempty}
                                    </select>
                                    <script>
                                        $(function () {
                                            var option = {};
                                            var selectEl = $("#search_{$search.name|default=''}");
                                            {if !$search.param}
                                            // 启用ajax分页查询
                                            option = {
                                                language: "zh-CN",
                                                ajax: {
                                                    delay: 250, // 限速请求
                                                    url: "{$search.param['select_url']}",   //  请求地址
                                                    dataType: 'json',
                                                    data: function (params) {
                                                        return {
                                                            keyWord: params.term || '',    //搜索参数
                                                            page: params.page || 1,        //分页参数
                                                            rows: params.page_size || 10,   //每次查询10条记录
                                                        };
                                                    },
                                                    processResults: function (data, params) {
                                                        params.page = params.page || 1;
                                                        if (params.page === 1) {
                                                            data.data.unshift({id: '', name: "", text: "所有"});
                                                        }
                                                        return {
                                                            results: data.data || [],
                                                            pagination: {
                                                                more: (params.page) < data.last_page
                                                            }
                                                        };
                                                    },
                                                    cache: true
                                                }
                                            };
                                            // 默认值设置：异步回填，避免阻塞渲染
                                            var defaultValue = selectEl.data("value");
                                            if (defaultValue) {
                                                $.post("{$search.param['select_url']}", {value: defaultValue}, function (data) {
                                                    selectEl.append($("<option/>", {value: data.key, text: data.value, selected: true}));
                                                    selectEl.val(String(data.key)).trigger("change");
                                                }, "json").always(function () {
                                                    selectEl.select2(option);
                                                });
                                            } else {
                                                selectEl.select2(option);
                                            }
                                            {/if}
                                            {if $search.param}
                                            selectEl.select2(option);
                                            {/if}
                                        })
                                    </script>
                                    {else}
                                    <input type="text" id="search_{$search.name|default=''}" name="{$search.name|default=''}" value="{$search.default|default=''}"/>
                                    {/if}
                                    {/if}
                                </li>
                                {/volist}
                                <li>
                                    <button type="button" class="btn btn-primary btn-rounded btn-sm" data-action="table-search"><i class="fa fa-search"></i>&nbsp;{:L('搜索')}</button>
                                    <button type="button" class="btn btn-warning btn-rounded btn-sm" data-action="form-reset"><i class="fas fa-sync-alt"></i>&nbsp;{:L('重置')}</button>
                                </li>
                            </ul>
                            {/notempty}
                        </div>
                    </form>
                </fieldset>
            </div>
            {/if}
            <!--列表区域开始-->
            <div class="col-sm-12 select-table table-striped">
                <div class="btn-group-sm" id="toolbar" role="group">
                {volist name="top_buttons" id="top_button"}
                <a class="{$top_button.class|default=''}" {if isset($top_button['href']) && $top_button['href']}href="{$top_button.href|default=''}"{/if}{if isset($top_button['target']) && $top_button['target']} target="{$top_button.target|default=''}"{/if}{if isset($top_button['data']) && is_array($top_button['data'])}{foreach $top_button.data as $dkey=>$dval} data-{$dkey}="{$dval|raw}"{/foreach}{/if}{if isset($top_button['onclick_html'])}{$top_button.onclick_html|raw}{/if} title="{$top_button.title|default=''}" {if isset($top_button['url']) && $top_button['url']} data-url="{$top_button.url|default=''}"{/if}>
                    <i class="{$top_button.icon|default=''}"></i> {:L($top_button.title)}
                </a>
                {/volist}
                </div>

                {if !empty($second_group)}
                <div class="w-100 bg-white mb-2">
                    <ul class="nav nav-tabs nav-tabs-alt js-tabs-enabled">
                        {volist name="second_group" id="item" key="items_key"}
                        <li class="nav-item">
                            <a class="nav-link {if $item.active}active{/if}" href="{$item.link}" data-pjax="wrapMain">{:L($item.title)}</a>
                        </li>
                        {/volist}
                    </ul>
                </div>
                {/if}

                <table id="bootstrap-table" data-mobile-responsive="true"></table>
            </div>
        </div>
    </div>
    <script>
        $(function() {
            const stateUrl = {:json_encode($state_url ?? '', JSON_UNESCAPED_SLASHES + JSON_HEX_TAG + JSON_HEX_APOS + JSON_HEX_QUOT + JSON_HEX_AMP)};
            const submitUrl = {:json_encode($submit_url ?? '', JSON_UNESCAPED_SLASHES + JSON_HEX_TAG + JSON_HEX_APOS + JSON_HEX_QUOT + JSON_HEX_AMP)};
            let columns = {:json_encode($columns, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES + JSON_HEX_TAG + JSON_HEX_APOS + JSON_HEX_QUOT + JSON_HEX_AMP)};
            let right_buttons = {:json_encode($right_buttons, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES + JSON_HEX_TAG + JSON_HEX_APOS + JSON_HEX_QUOT + JSON_HEX_AMP)};
            let list=[];
            let tmp =[]
            tmp.push({
                checkbox: {$hide_checkbox ? 'true' : 'false'},
                formatter: function(value, row, index) {
                    if(row.checkbox_disabled =='1'){
                        return {
                            disabled : true
                        }
                    }
                }
            });
            $.each(columns, function(i, item) {
                var sortable = item.name=='{$unique_id}' || item.sortable == 'true';
                var class1 = item.class==='' ? '' : item.class;
                var format;
                var editable;
                if (item.name === 'sort')
                {
                    format = function(value, row, index) {
                        var val = value?value:item.default;
                        return '<input class="form-control input-sm w_40 changeSort" type="text" value="' + escapeAttr(val) + '" data-field="sort" data-id="' + escapeAttr(row.{$unique_id}) + '" data-action="table-sort">';
                    }
                }else{
                    switch (item.type){
                        case 'icon':
                        case 'text':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                return HTMLDecode(val);
                            }
                            break;
                        case 'input':
                        case 'sort':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                return '<input class="form-control input-sm w_40 changeSort" type="text" value="' + escapeAttr(val) + '" data-field="' + escapeAttr(item.name) + '" data-id="' + escapeAttr(row.{$unique_id}) + '" data-action="table-sort">';
                            }
                            break;
                        case 'datetime':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                return changeDateFormat(val);
                            }
                            break;
                        case 'radio':
                        case 'status':
                            format = function(value, row, index) {
                                var state = value === 0 ? 'off' : 'on';
                                return '<i class="fa fa-toggle-' + state + ' text-primary fa-2x cursor_pointer" data-action="operate-state" data-id="' + escapeAttr(row.{$unique_id}) + '" data-field="' + escapeAttr(item.name) + '" data-url="' + escapeAttr(stateUrl) + '" role="button" tabindex="0" aria-label="切换状态"></i>';
                            }
                            break;

                        case 'tag':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                if(item.param)
                                {
                                    return '<span class="badge text-bg-primary">'+HTMLDecode(item.param[val])+'</span>'
                                }else{
                                    return '<span class="badge text-bg-primary">'+HTMLDecode(val)+'</span>'
                                }
                            }
                            break;

                        case 'label':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                if(item.param)
                                {
                                    return '<span class="badge badge-'+escapeHtml(item.param[val].label)+'">'+HTMLDecode(item.param[val].text)+'</span>'
                                }else{
                                    return '<span class="badge text-bg-primary">'+HTMLDecode(val)+'</span>'
                                }
                            }
                            break;

                        case 'bool':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                if (val == 0) {
                                    return '<i class="fa fa-ban text-danger"></i>';
                                } else if (val == 1) {
                                    return '<i class="fa fa-check text-primary"></i>';
                                }
                            }
                            break;

                        case 'link':
                            format = function(value, row, index) {
                                var link = item.default;
                                var reg = /__(.*?)__/g;
                                var result ;
                                var val = value?value:item.default;
                                while (result = reg.exec(link)) {
                                    link = link.replace(result[0], escapeAttr(row[result[1]]));
                                }
                                // 拼接（所有服务端字段先转义，避免拼接型 XSS）
                                link = '<a href="'+escapeAttr(link)+'" class="'+escapeAttr(item.class)+'" target="_blank" title="'+escapeAttr(item.title)+'">' + escapeHtml(val) + '</a>';
                                return link;
                            }
                            break;

                        case 'dialog':
                            format = function(value, row, index) {
                                var link = item.default;
                                var reg = /__(.*?)__/g;
                                var result ;
                                var val = value?value:item.default;
                                while (result = reg.exec(link)) {
                                    link = link.replace(result[0], escapeAttr(row[result[1]]));
                                }
                                // 拼接
                                link = '<a href="javascript:;" data-url="'+escapeAttr(link)+'" class="dask-ajax-open '+escapeAttr(item.class)+'" data-title="'+escapeAttr(item.title)+'" title="'+escapeAttr(item.title)+'">' + escapeHtml(val) + '</a>';
                                return link;
                            }
                            break;

                        case 'image':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                if (DaskAdmin.common.isNotEmpty(val)) {
                                    return '<a href="' + escapeAttr(val) + '" data-fancybox="fancybox" data-fancybox-group="dask-thumb"> <img class="image_preview" src="' + escapeAttr(val) + '"></a>';
                                }
                            }
                            break;

                        case 'color':
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                if (DaskAdmin.common.isNotEmpty(value)) {
                                    return '<i class="table_colorpicker" style="background: ' + escapeAttr(val) + '""></i>';
                                }
                            }
                            break;

                        case 'select':
                            format = function(value, row, index) {
                                var val1 = value?value:item.default;
                                var str = '<select class="form-control" data-action="operate-select" data-id="' + escapeAttr(row.{$unique_id}) + '" data-field="' + escapeAttr(item.name) + '">';
                                str+='<option  value="0">--请选择--</option>'
                                $.each(item.param,function(val,name) {
                                    var selected = '';
                                    if(val1==val)
                                    {
                                        selected ='selected=selected';
                                    }
                                    str+='<option  value="'+escapeAttr(val)+'" '+ selected+'>'+escapeHtml(name)+'</option>'
                                });
                                str +='</select>';
                                return str;
                            }
                            break;

                        // 保持原有 fallthrough：checkbox 复用 select2 的渲染逻辑
                        case 'checkbox':
                        case 'select2':
                            format = function(value, row, index) {
                                var param = JSON.stringify(item.param);
                                var val = value?value:item.default;
                                var  str = '<select style="min-width:150px" class="select2 table-select2" data-id="'+escapeHtml(row.{$unique_id})+'" data-name="'+escapeHtml(item.name)+'" data-options=\''+escapeAttr(param)+'\' data-value="'+escapeHtml(val)+'" multiple ></select>';
                                return str;
                            }
                            break;

                        case 'btn':
                            format = function(value, row, index) {
                                var actions = [];
                                $.each(right_buttons, function(i2, item2) {
                                    var confirm = '';
                                    if(typeof item2.confirm == 'undefined' || item2.confirm =='')
                                    {
                                        confirm = '';
                                    }else{
                                        confirm = 'data-confirm="'+escapeAttr(item2.confirm)+'"';
                                    }
                                    // 服务端下发的 class/title/icon/target 全部转义后再拼 HTML
                                    var cls = escapeAttr(item2.class);
                                    var title = escapeHtml(item2.title);
                                    var titleAttr = escapeAttr(item2.title);
                                    var icon = escapeAttr(item2.icon);
                                    var target = escapeAttr(item2.target);
                                    if(item2.type == 'edit'){
                                        if(item2.href){
                                            var url = item2.href;
                                            var reg = /__(.*?)__/g;
                                            var result;
                                            while (result = reg.exec(url)) {
                                                url = url.replace(result[0], escapeAttr(row[result[1]]));
                                            }
                                            actions.push('<a class="m-1 '+cls+'" title="'+titleAttr+'" target="'+target+'" href="'+escapeAttr(url)+'"><i class="'+icon+'"></i> '+title+'</a> ');
                                        }else{
                                            actions.push('<a class="m-1 '+cls+'" href="javascript:void(0)" title="'+titleAttr+'" data-action="operate-edit" data-id="'+escapeAttr(row.{$unique_id})+'"><i class="'+icon+'"></i>'+title+'</a> ');
                                        }
                                    } else if (item2.type == 'delete'){
                                        if(item2.href){
                                            var url = item2.href;
                                            var reg = /__(.*?)__/g;
                                            var result;
                                            while (result = reg.exec(url)) {
                                                url = url.replace(result[0], escapeAttr(row[result[1]]));
                                            }
                                            actions.push('<a class="m-1 '+cls+'" target="'+target+'" title="'+titleAttr+'" href="'+escapeAttr(url)+'"><i class="'+icon+'"></i> '+title+'</a> ');
                                        }else{
                                            actions.push('<a class="m-1 '+cls+'" href="javascript:void(0)" title="'+titleAttr+'" data-action="operate-remove" data-id="'+escapeAttr(row.{$unique_id})+'"><i class="'+icon+'"></i> '+title+'</a> ');
                                        }
                                    }else {
                                        if(item2.href && !item2.url && item2.href!=='javascript:;'){
                                            var url = item2.href;
                                            var reg = /__(.*?)__/g;
                                            var result;
                                            while (result = reg.exec(url)) {
                                                url = url.replace(result[0], escapeAttr(row[result[1]]));
                                            }
                                            actions.push('<a class="m-1 '+cls+'" target="'+target+'" title="'+titleAttr+'" href="'+escapeAttr(url)+'"><i class="'+icon+'"></i> '+title+'</a> ');
                                        }else{
                                            var url = item2.url;
                                            var reg = /__(.*?)__/g;
                                            var result;
                                            while (result = reg.exec(url)) {
                                                url = url.replace(result[0], escapeAttr(row[result[1]]));
                                            }
                                            actions.push('<a class="m-1 '+cls+'" title="'+titleAttr+'" data-url="'+escapeAttr(url)+'"' +confirm +'><i class="'+icon+'"></i> '+title+'</a> ');
                                        }
                                    }
                                })
                                return actions.join('');
                            }
                            break;

                        //单列提交
                        case 'submit':
                            format = function(value, row, index) {
                                var payload = encodeURIComponent(JSON.stringify(row));
                                var title = value ? value : item.title;
                                return '<a class="m-1 btn btn-sm btn-primary '+escapeAttr(item.class)+'" title="'+escapeAttr(title)+'" data-action="api-post" data-url="'+escapeAttr(submitUrl)+'" data-payload="'+payload+'" href="javascript:;" role="button" tabindex="0">'+escapeHtml(title)+'</a> ';
                            }
                            break;

                        default :
                            format = function(value, row, index) {
                                var val = value?value:item.default;
                                return HTMLDecode(val);
                            }
                    }
                }
                var data={field: item.name,
                    title: item.title,
                    sortable:sortable,
                    class:class1,
                    formatter: format || function (value) { return HTMLDecode(value); },
                    editable: editable || undefined
                }
                tmp.push(data)
            });
            list.push(tmp);
            let column = list.reduce(function(reduced,next){
                    Object.keys(next).forEach(function(key){reduced[key]=next[key];});
                    return reduced;
                }
            );

            let options = {
                uniqueId      : "{$unique_id}",         // 表格主键名称，（默认为id，如表主键不为id必须设置主键）
                url           : "{$data_url|raw}",      // 请求后台的URL
                addUrl        : "{$add_url|raw}",       // 新增的地址
                editUrl       : "{$edit_url|raw}",      // 修改的地址
                delUrl        : "{$del_url|raw}",       // 删除的地址
                exportUrl     : "{$export_url|raw}",    // 导出的地址
                sortUrl       : "{$sort_url|raw}",      // 排序的地址
                chooseUrl     : "{$choose_url|raw}",    // 下拉选择|radio单选
                sortName      : "{$unique_id}",         // 排序列名称
                sortOrder     : "desc",                 // 排序方式  asc 或者 desc
				pagination    : {$pagination},			// 是否进行分页
                parentIdField : "{$parent_id_field}",   // 列表树模式需传递父id字段名（parent_id/pid）
				clickToSelect : false,				    // 默认false不响应，设为true则当点击此行的某处时，会自动选中此行的checkbox/radiobox
                pageSize      : "{$page_size}",         // 每页显示的行数
                columns:column,
            };

            DaskAdmin.table.init(options);
        });

        function select(element,id,field)
        {
            var value = $('#'+element+' option:selected') .val();
            var url = DaskAdmin.common.isEmpty(id) ? DaskAdmin.table._option.selectUrl : DaskAdmin.table._option.selectUrl.replace("__id__", id);
            var data = {"id": id,"field":field,"value":value};
            DaskAdmin.operate.submit(url, "post", "json", data);
        }

        // 搜索
        function searchPre() {
            var data = {};
            DaskAdmin.table.search('', data);
        }

        // 重置搜索
        function resetPre() {
            DaskAdmin.form.reset();
        }

		//HTML反转义：用 DOMParser 在离屏文档中解析，避免 innerHTML 触发 <img onerror> 之类载荷
		function HTMLDecode(text) {
			var raw = String(text == null ? '' : text);
			if (window.DOMParser) {
				try {
					var doc = new DOMParser().parseFromString(raw, 'text/html');
					return doc.body.textContent || doc.body.innerText || '';
				} catch (e) {}
			}
			var temp = document.createElement("div");
			temp.textContent = raw;
			return temp.textContent || '';
		}

		// 服务端字段拼 HTML 前统一转义；escapeAttr 用于属性，escapeHtml 用于文本节点
		function escapeAttr(value) {
			return String(value == null ? '' : value)
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		function escapeHtml(value) {
			return escapeAttr(value);
		}

		function getDefaultValue(value,text)
        {
            return value ? value : text;
        }
    </script>
    <!--底部提示-->
    {if $page_tips_bottom}
    <div class="alert alert-{$tips_type} alert-dismissable">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <p>{$page_tips_bottom|raw}</p>
    </div>
    {/if}

    <!--额外HTML代码-->
    {$extra_html_bottom|raw|default=''}

    <!--额外JS代码-->
    {$extra_js|raw|default=''}
</section>
{/block}


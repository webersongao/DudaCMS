{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>input('uid')])}
            <div class="col-md-10">
                <div class="block mb-1 new-block">
                    <div class="block-header">
                        <b>{:L('我的关注')}</b>
                        <div class="float-end">
                            <div class="mod-head-more dropdown show">
                                <a href="javascript:;" class="dropdown-toggle d-none-arrow dask-ui-focus-show" data-bs-toggle="dropdown">
                                    <span>{:L('关注的问题')}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end text-center dask-ui-focus-dropdown">
                                    <span class="arrow"></span>
                                    <ul>
                                        <li class="active dropdown-item" data-type="question"><a href="javascript:;">{:L('关注的问题')}</a></li>
                                        <li class="dropdown-item" data-type="friend"><a href="javascript:;">{:L('关注的人')}</a></li>
                                        <li class="dropdown-item" data-type="fans"><a href="javascript:;">{:L('关注我的')}</a></li>
                                        <li class="dropdown-item" data-type="column"><a href="javascript:;">{:L('关注的专栏')}</a></li>
                                        <li class="dropdown-item" data-type="topic"><a href="javascript:;">{:L('关注的话题')}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tabMain" class="py-2 bg-white dask-common-list"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function (){
        var url = baseUrl + '/focus/list/';
        var uid = parseInt("{$user.uid}");
        var allowedTypes = ['question','friend','fans','column','topic'];
        var initialType = "{:htmlspecialchars((string)input('type','question'))}";
        if (allowedTypes.indexOf(initialType) === -1) initialType = 'question';

        function activateType(type) {
            $('.dask-ui-focus-dropdown ul li').removeClass('active');
            $('.dask-ui-focus-dropdown ul li[data-type="' + type + '"]').addClass('active');
            $('.dask-ui-focus-show').find('span').text($('.dask-ui-focus-dropdown ul li[data-type="' + type + '"] a').text());
        }

        activateType(initialType);
        Dask.api.ajaxLoadMore('#tabMain',url,{type:initialType,uid:uid},null,'json');

        $('.dask-ui-focus-dropdown ul li').click(function ()
        {
            var type = $(this).data('type');
            if (allowedTypes.indexOf(type) === -1) return;
            activateType(type);
            $('#tabMain').empty();
            Dask.api.ajaxLoadMore('#tabMain',url,{type:type,uid:uid},null,'json');
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', baseUrl + '/focus?type=' + encodeURIComponent(type));
            }
        });
    })
</script>
{/block}

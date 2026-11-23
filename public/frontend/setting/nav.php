<style>
.dask-ui-nav-container.dask-card{margin-bottom:16px;overflow:hidden;}
.dask-ui-nav-container .dask-pjax-tabs{flex-wrap:nowrap;overflow-x:auto;overscroll-behavior-x:contain;scrollbar-width:none;}
.dask-ui-nav-container .dask-pjax-tabs::-webkit-scrollbar{display:none;}
.dask-ui-nav-container .nav-link{min-height:44px;display:inline-flex;align-items:center;white-space:nowrap;}
</style>
<div class="dask-ui-nav-container clearfix bg-white dask-card">
    <div class="dask-card__head">
        <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block">
            <li class="nav-item"><a class="nav-link {if $thisAction=='profile'}active {/if}" data-pjax="tabMain" href="{:url('setting/profile')}">{:L('账号设置')}</a></li>
            <li class="nav-item"><a class="nav-link {if $thisAction=='notify'}active {/if}" data-pjax="tabMain" href="{:url('setting/notify')}">{:L('消息通知')}</a></li>
            <li class="nav-item"><a class="nav-link {if $thisAction=='security'}active {/if}" data-pjax="tabMain" href="{:url('setting/security')}">{:L('安全设置')}</a></li>
            {if !empty($thirdConfig['base']['enable'])}
            <li class="nav-item"><a class="nav-link {if $thisAction=='openid'}active {/if}" data-pjax="tabMain" href="{:url('setting/openid')}">{:L('账号绑定')}</a></li>
            {/if}
            <li class="nav-item"><a class="nav-link {if $thisAction=='verified'}active {/if}" data-pjax="tabMain" href="{:url('setting/verified')}">{:L('用户认证')}</a></li>
            <!--用户设置导航-->
            
        </ul>
    </div>
</div>

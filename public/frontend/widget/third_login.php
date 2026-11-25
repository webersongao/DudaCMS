{if in_array('qq',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'qq'])}" class="dask-social-link" data-platform="qq" title="{:L('QQ 登录')}" aria-label="{:L('QQ 登录')}">
    <img src="{:asset('common/image/third_auth/qq.svg', false)}" alt="" aria-hidden="true">
</a>
{/if}

{if in_array('wechat',$thirdConfig['base']['enable'])}
{if $isMobile}
<a href="{:url('ThirdAuth/connect',['platform'=>'wechat'])}" class="dask-social-link" data-platform="wechat" title="{:L('微信登录')}" aria-label="{:L('微信登录')}">
{else/}
<a href="#" role="button" data-url="{:url('ThirdAuth/qrcode')}" data-width="100" class="dask-social-link dask-ajax-open" data-platform="wechat" title="{:L('微信扫码登录')}" aria-label="{:L('微信扫码登录')}">
{/if}
    <img src="{:asset('common/image/third_auth/wechat.svg', false)}" alt="" aria-hidden="true">
</a>
{/if}

{if in_array('weibo',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'weibo'])}" class="dask-social-link" data-platform="weibo" title="{:L('微博登录')}" aria-label="{:L('微博登录')}">
    <img src="{:asset('common/image/third_auth/weibo.svg', false)}" alt="" aria-hidden="true">
</a>
{/if}

{if in_array('github',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'github'])}" class="dask-social-link" data-platform="github" title="{:L('GitHub 登录')}" aria-label="{:L('GitHub 登录')}">
    <img src="{:asset('common/image/third_auth/github.svg', false)}" alt="" aria-hidden="true">
</a>
{/if}

{if in_array('google',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'google'])}" class="dask-social-link" data-platform="google" title="{:L('Google 登录')}" aria-label="{:L('Google 登录')}">
    <img src="{:asset('common/image/third_auth/google.svg', false)}" alt="" aria-hidden="true">
</a>
{/if}

{if in_array('twitter',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'twitter'])}" class="dask-social-link" data-platform="x" title="{:L('Twitter/X 登录')}" aria-label="{:L('Twitter/X 登录')}">
    <img src="{:asset('common/image/third_auth/x.svg', false)}" alt="" aria-hidden="true">
</a>
{/if}

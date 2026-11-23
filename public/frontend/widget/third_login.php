{if in_array('qq',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'qq'])}" class="me-2" target="_blank" title="QQ 登录"><i class="fab fa-qq text-primary" style="font-size: 2rem"></i></a>
{/if}

{if in_array('wechat',$thirdConfig['base']['enable'])}
{if $isMobile}
<a href="{:url('ThirdAuth/connect',['platform'=>'wechat'])}" class="me-2" target="_blank" title="微信登录"><i class="fab fa-wechat text-success" style="font-size: 2rem"></i></a>
{else/}
<a data-url="{:url('ThirdAuth/qrcode')}" href="javascript:;" data-width="100" target="_blank" class="me-2 dask-ajax-open" title="微信扫码登录"><i class="fab fa-wechat text-success" style="font-size: 2rem"></i></a>
{/if}
{/if}

{if in_array('weibo',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'weibo'])}" class="me-2" target="_blank" title="微博登录"><i class="fab fa-weibo text-warning" style="font-size: 2rem"></i></a>
{/if}

{if in_array('github',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'github'])}" class="me-2" target="_blank" title="GitHub 登录"><i class="fab fa-github text-dark" style="font-size: 2rem"></i></a>
{/if}

{if in_array('google',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'google'])}" class="me-2" target="_blank" title="Google 登录"><i class="fab fa-google text-danger" style="font-size: 2rem"></i></a>
{/if}

{if in_array('twitter',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'twitter'])}" class="me-2" target="_blank" title="Twitter/X 登录"><i class="fab fa-twitter text-info" style="font-size: 2rem"></i></a>
{/if}

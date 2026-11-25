{if in_array('qq',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'qq'])}" class="dask-social-link" data-platform="qq" title="{:L('QQ 登录')}" aria-label="{:L('QQ 登录')}">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path fill="#12B7F5" d="M12 2.2c-3.4 0-5.9 2.6-5.9 5.9v3.1c0 1.2-.6 2.1-1.6 3-.8.8-1.2 1.6-1.2 2.6 0 2 3.8 3.6 8.7 3.6s8.7-1.6 8.7-3.6c0-1-.4-1.8-1.2-2.6-1-.9-1.6-1.8-1.6-3V8.1c0-3.3-2.5-5.9-5.9-5.9Z"/>
        <path fill="#fff" d="M8.1 15.8c.6.4 1.6.7 2.3.2.5-.4.3-1-.2-1.2-.8-.3-1.8-.2-2.4.2-.5.3-.4.7.3.8Zm7.8 0c-.6.4-1.6.7-2.3.2-.5-.4-.3-1 .2-1.2.8-.3 1.8-.2 2.4.2.5.3.4.7-.3.8Z"/>
        <path fill="#fff" d="M12 17.4c-1.1 0-2 .4-2 .9s.9.9 2 .9 2-.4 2-.9-.9-.9-2-.9Z"/>
        <path fill="#FF6B6B" d="M9.7 20.1c.7.7 1.5 1.2 2.3 1.2s1.6-.5 2.3-1.2c-1.4-.5-3.2-.5-4.6 0Z"/>
    </svg>
</a>
{/if}

{if in_array('wechat',$thirdConfig['base']['enable'])}
{if $isMobile}
<a href="{:url('ThirdAuth/connect',['platform'=>'wechat'])}" class="dask-social-link" data-platform="wechat" title="{:L('微信登录')}" aria-label="{:L('微信登录')}">
{else/}
<a href="#" role="button" data-url="{:url('ThirdAuth/qrcode')}" data-width="100" class="dask-social-link dask-ajax-open" data-platform="wechat" title="{:L('微信扫码登录')}" aria-label="{:L('微信扫码登录')}">
{/if}
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path fill="#07C160" d="M10.2 3.4c-4.3 0-7.8 2.9-7.8 6.5 0 2.1 1.2 4 3.1 5.2l-.8 2.7 3.1-1.6c.8.2 1.6.3 2.4.3h.6a6.6 6.6 0 0 1-.2-1.7c0-3.5 3.4-6.4 7.6-6.4h.5c-.9-2.8-4-4.9-8.5-4.9Z"/>
        <circle cx="7.3" cy="9.1" r="1" fill="#fff"/>
        <circle cx="12.6" cy="9.1" r="1" fill="#fff"/>
        <path fill="#2DC100" d="M22 14.4c0-3-3-5.5-6.7-5.5s-6.7 2.5-6.7 5.5 3 5.5 6.7 5.5c.8 0 1.5-.1 2.2-.4l3 1.5-.9-2.5c1.5-1 2.4-2.5 2.4-4.1Z"/>
        <circle cx="13.7" cy="13.7" r=".85" fill="#fff"/>
        <circle cx="18" cy="13.7" r=".85" fill="#fff"/>
    </svg>
</a>
{/if}

{if in_array('weibo',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'weibo'])}" class="dask-social-link" data-platform="weibo" title="{:L('微博登录')}" aria-label="{:L('微博登录')}">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <circle cx="12" cy="12" r="10" fill="#E6162D"/>
        <ellipse cx="12" cy="12.2" rx="6.5" ry="4.4" fill="#fff"/>
        <circle cx="12" cy="12.2" r="2.2" fill="#E6162D"/>
        <circle cx="12" cy="12.2" r=".8" fill="#fff"/>
        <path d="M16.8 4.5c1.8.7 3.1 2.1 3.7 3.9" fill="none" stroke="#FFB400" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
</a>
{/if}

{if in_array('github',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'github'])}" class="dask-social-link" data-platform="github" title="{:L('GitHub 登录')}" aria-label="{:L('GitHub 登录')}">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path fill="#181717" d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.57.1.78-.25.78-.55v-2.17c-3.2.7-3.88-1.36-3.88-1.36-.52-1.34-1.28-1.7-1.28-1.7-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.23-1.28-5.23-5.69 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.18 1.18A11.1 11.1 0 0 1 12 6.22c1.98 0 4 .27 5.87.8 2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.12 3.05.74.81 1.18 1.84 1.18 3.1 0 4.42-2.69 5.39-5.25 5.68.41.36.78 1.06.78 2.14v3.17c0 .3.2.66.79.55C20.21 21.38 23.5 17.08 23.5 12 23.5 5.65 18.35.5 12 .5Z"/>
    </svg>
</a>
{/if}

{if in_array('google',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'google'])}" class="dask-social-link" data-platform="google" title="{:L('Google 登录')}" aria-label="{:L('Google 登录')}">
    <svg viewBox="0 0 48 48" aria-hidden="true" focusable="false">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65Z"/>
        <path fill="#FBBC05" d="M10.53 28.59A14.6 14.6 0 0 1 9.77 24c0-1.6.27-3.14.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19Z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
    </svg>
</a>
{/if}

{if in_array('twitter',$thirdConfig['base']['enable'])}
<a href="{:url('ThirdAuth/connect',['platform'=>'twitter'])}" class="dask-social-link" data-platform="x" title="{:L('Twitter/X 登录')}" aria-label="{:L('Twitter/X 登录')}">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path fill="#111" d="M18.9 2H22l-6.78 7.75L23.2 22h-6.25l-4.9-6.41L6.3 22H3.2l7.25-8.29L2.8 2h6.4l4.43 5.86L18.9 2Zm-1.1 18h1.73L7.35 3.9H5.5L17.8 20Z"/>
    </svg>
</a>
{/if}

<div class="w-top-img home-hero" style="background: linear-gradient(90deg, rgba(0,37,90,.62), rgba(0,37,90,.28)), url('/static/brand/hero-home.jpg') center center; background-size:cover;">
    {include file="global/nav"}
    <div class="container index-search">
        <div class="row">
            <h2 class="col-12">{$theme_config['home']['search_title_text']|default=$heroTitle}</h2>
            <p class="mb-3 w-100" style="color: #eee">{$theme_config['home']['search_min_text']|default=$heroSubtitle}</p>
            <div class="col-12 mt-3 hero-search">
                <form action="{:url('search/index')}" method="get" id="homeSearch">
                <span>
                    <i class="iconfont">&#xe610;</i>
                    <input type="text" autocomplete="off" value="{:input('get.q')}" name="q" placeholder="{:L('在装逼方面遇到了问题？')}" required>
                    <button type="button" class="btn gradientBtn px-4 ms-1" style="height: 42px;" data-action="form-submit" data-form="#homeSearch">{:L('搜索')}</button>
                </span>
                </form>
            </div>
        </div>
    </div>
</div>

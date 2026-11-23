{if !empty($category_list) && get_setting('enable_category')=='Y'}
<style>
    .dask-category-tabs .swiper-slide { width: auto !important; }
</style>
<div class="container my-2 dask-category-tabs" style="padding:0">
    <div class="row n-tab align-content-center">
        <div class="swiper swiper-container" style="margin: 0">
            <ul class="nav nav-pills n-nav swiper-wrapper" style="flex-wrap: nowrap;">
                <li class="nav-item swiper-slide">
                    <a class="nav-link mb-0 {if !$category}active c-active{/if}" data-k="0" data-pjax="wrapMain" href="{:url($thisRequest,['sort'=>$sort,'category_id'=>0])}">{:L('全部分类')}</a>
                </li>
                {foreach $category_list as $k => $v}
                <li class="nav-item swiper-slide">
                    <a class="nav-link {if $category==$v.id} active c-active{/if}" data-k="{$k+1}" data-pjax="wrapMain" href="{:url($thisRequest,['sort'=>$sort,'category_id'=>$v.id])}">{$v.title}</a>
                </li>
                {/foreach}
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        let c_a = $('.dask-category-tabs .c-active').first(),
            k = parseInt(c_a.data('k') || 0)
        new Swiper('.dask-category-tabs .swiper.swiper-container', {
            speed: 600,
            grabCursor: true,
            slidesPerView: "auto",
            initialSlide: k,
            slidesPerGroup: 3
        })
    })
</script>
{/if}

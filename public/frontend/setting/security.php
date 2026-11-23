{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>input('uid')])}
            <div class="col-md-10">
                {include file="setting/nav"}
                <div class="bg-white mt-1 p-3" id="tabMain">
                    <div class="mb-3 row mt-3">
                        <label class="col-md-2 col-form-label"> {:L('账号密码')} </label>
                        <div class="col-md-6 d-flex flex-wrap align-items-center gap-3">
                            <input type="password" class="form-control flex-fill" placeholder="******" name="password" value="">
                            <button class="btn btn-primary dask-ajax-open" data-url="{:url('account/modify_password')}">{:L('修改密码')}</button>
                        </div>
                    </div>

				</div>
			</div>
		</div>
	</div>
</div>
{/block}
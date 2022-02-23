<div class="share floating-block sticky-top">
    <h2 class="mb-3">Subscribe to Our Newsletter</h2>
    <p>Subscribe for the hottest scholarship news!</p>
    <form method="POST" action="/subscribe">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <input type="email" class="form-control mb-2" placeholder="Enter email"/>
        <input type="submit" value="Subscribe" class="btn btn-primary btn-block">
    </form>
</div>

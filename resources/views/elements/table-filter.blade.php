<div class="d-flex align-items-center pr-3">
    <div class="btn-group">
                        <span  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="h-pill h-pill-primary rounded search-back-button d-flex justify-content-center align-items-center" data-toggle="collapse" href="#colappsableFilters" role="button" aria-expanded="false" aria-controls="colappsableFilters">
                             @include('elements.icon',['icon'=>'filter-outline','variant'=>'medium','centered'=>true])
                        </span>
        <div class="dropdown-menu dropdown-menu-right mt-1">
            <form class="px-4 py-3 card shadow-sm">
                <div class="form-group">
                    <label for="exampleDropdownFormEmail1">Email address</label>
                    <input type="email" class="form-control" id="exampleDropdownFormEmail1" placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label for="exampleDropdownFormPassword1">Password</label>
                    <input type="password" class="form-control" id="exampleDropdownFormPassword1" placeholder="Password">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="dropdownCheck">
                    <label class="form-check-label" for="dropdownCheck">
                        Remember me
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Sign in</button>
            </form>
        </div>
    </div>
</div>

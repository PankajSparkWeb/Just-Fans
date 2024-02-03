<div class="new-side-bar-container sidebar-outer">
    <div class="new-side-bar">

        {{-- first card --}}
        @if (Auth::check())
        <div class='user_name'>
            <img src="{{ Auth::user()->avatar }}" class="home-user-avatar">
            <div class="text-truncate max-width-150">{{ Auth::user()->name }}</div>
        </div>
          <div class="interest-count-percentage">        
            @php
            $users_interests = get_users_learned_posts_interests();
            @endphp        
            @if (count($users_interests) > 0)
                <ul class='user_interest_count'>
                    @foreach ($users_interests as $key => $interestCount)
                        <li>
                            <span>{{ $key }} ____ {{ $interestCount['total_posts'] }} ({{ $interestCount['percentage'] }})</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No user interests available.</p>
            @endif
        </div>
        <div class="feed-suggession-card">
        @include('elements.feed.suggestions-box',['profiles'=>$suggestions,'isMobile' => false])
        </div>
        @else
        <div class="about-community-card  ">
            <div class="about-comunity-text-area new-side-bar-card">
                <div class="side-bar-heading">
                    <h3>About Community</h3>
                </div>
                <div class="card-lower-body">
                    <div class="discription">
                        <p class='show-description'>You learn something new every day; what did you learn today? Submit
                            interesting and specific
                            facts about something that you just found out here.</p>
                        <p class='show-dated'>Created Dec 28, 2008</p>
                    </div>

                    <div class="about-comunity-member-area">
                        <div class="mambers">
                            <h1>200M</h1>
                            <p>Members</p>

                        </div>
                        <div class="Online">
                            <h1>200</h1>
                            <p>Online</p>

                        </div>
                        <div class="ranked-by-size">

                            <h1>#6</h1>
                            <p>Ranked by Size</p>
                        </div>
                    </div>

                    <div class="about-comunity-createPost"><a href="{{ route('posts.create') }}">Create Post</a></div>
                    <div class="about-community-options">
                        <div class="accordion side-bar-accordian">
                            <div class="accordion-item">
                                <div class="accordion-header accr-header-top">Community Options</div>
                                <div class="accordion-content">
                                    <li><a href="#" id="comunity_theameClick">Comunity Theame</a></li>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Second Card --}}
            <div class="rules-card new-side-bar-card new-side-bar-card">
                <div class="side-bar-heading">
                    <h3>Rules</h3>
                </div>

                <div class="card-lower-body">
                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">1. Inaccurate/unverifiable/not supported by source</div>
                            <div class="accordion-content">
                                <p>Please link directly to a reliable source that supports every claim in your post
                                    title.
                                    Images alone do not count as valid references. Videos are fine so long as they come
                                    from
                                    reputable sources (e.g. BBC, Discovery, etc).</p>
                            </div>
                        </div>
                    </div>


                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">2. No personal opinions/anecdotes/subjective posts</div>
                            <div class="accordion-content">
                                <p>(e.g "TIL xyz is a great movie")</p>
                            </div>
                        </div>
                    </div>


                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">3. No recent sources </div>
                            <div class="accordion-content">
                                <p>Any sources (blog, article, press release, video, etc.) more recent than two months
                                    are not
                                    allowed.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">4. No politics/agenda pushing</div>
                            <div class="accordion-content">
                                <p>No submissions regarding or related to the following: recent politics, politicians,
                                    police
                                    misconduct, race/religion/gender, environmental issues, social issues, etc. See wiki
                                    for
                                    more detailed explanation</p>
                            </div>
                        </div>
                    </div>


                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">5. No misleading claims</div>
                            <div class="accordion-content">
                                <p>Posts that omit essential information, or present unrelated facts in a way that
                                    suggest a
                                    connection will be removed.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">6. Too general/can't stand on its own/how to</div>
                            <div class="accordion-content">
                                <p>a. Titles must begin with "TIL" or "Today I Learned" b. Make them descriptive,
                                    concise and
                                    specific (e.g. not "TIL something interesting about bacon"). c. Titles must be able
                                    to stand
                                    on their own without requiring readers to click on a link. d. "TIL about ..." and
                                    other
                                    broad posts don't belong on TIL. Try /r/Wikipedia, etc. instead, or be more specific
                                    (and
                                    avoid the word "about"). e. "TIL how to ..." posts belong on /r/HowTo.</p>
                            </div>
                        </div>
                    </div>


                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item">
                            <div class="accordion-header">7. No submissions about software/websites</div>
                            <div class="accordion-content">
                                <p>No submissions related to the usage, existence or features of specific
                                    software/websites
                                    (e.g. "TIL you can click on widgets in WidgetMaker 1.22").</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion side-bar-accordian accr-second-body">
                        <div class="accordion-item acc-eight">
                            <div class="accordion-header acc-eight-header">8 All NSFW links must be tagged.</div>
                        </div>
                    </div>


                </div>
            </div>
            @endif
            {{-- third card --}}
            <div class="wikipeadia-card new-side-bar-card new-side-bar-card">
                <div class="side-bar-wiki-heading side-bar-heading">
                    <h3>Wiki</h3>
                </div>
                <div class="card-lower-body">
                    <div class="side-bar-wiki-discription">
                        <p>Please see the wiki for more detailed explanations of the rules as
                            well as additional rules that may not be listed here</p>
                    </div>
                </div>
            </div>

            {{-- fourth card --}}
            {{-- <div class="moderators-card new-side-bar-card new-side-bar-card">
            <div class="side-bar-moderators-heading side-bar-heading">
                <h3>Moderators</h3>
                </div>
                <div class="card-lower-body">
            <div class="side-bar-discription">
                <div class="message-the-mode">
                    <a href="">Message The Mode</a>
                </div>
                <div class="moderators-links">
                    <li><a href="">u/wacrover</a></li>
                    <li><a href="">u/relic2279</a></li>
                    <li><a href="">u/lukemcr</a></li>
                    <li><a href="">u/Geekymumma</a></li>
                    <li><a href="">u/sdn</a></li>
                    <li><a href="">u/roger_</a></li>
                    <li><a href="">u/Lynda73</a></li>
                    <li><a href="">u/lanismycousin</a> 36 DD</li>
                    <li><a href="">u/roger_bot</a></li>
                    <li><a href="">u/TIL_mod</a> Does not answer PMs</li>
                </div>
            </div>
        </div>
        </div> --}}
        </div>
    </div>

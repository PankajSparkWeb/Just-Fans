<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<div class="blank-first-blue-block"></div>
    <div class="white-blank-div post-wiki-tabs">
        <div class="container">
            <div class="top-bar-area">
                <div class="top-white-bar-content">
                    <div class="today_i_learnd_logo"><img src="img\logos\TodayLearndLogo.png" alt="IMG"></div>
                    <div class="today_i_learnd_text_JoinBtn">
                        <div class="today_i_learnd_text">
                            <h2>Today I Learned (TIL)</h2>
                            <p>r/todayilearned</p>
                        </div>
                        <div class="today_i_learnd_Join&Bell">
                            <a href="">Joined</a>
                        </div>

                    </div>
                </div>
                <div class="top-white-bar-btns">
                    <a href="#" onclick="showContent('post')" id='post'>Post</a>
                    <a href="#" onclick="showContent('wiki')" id='wiki'>Wiki</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){    
            $('#post').addClass('add-class')
            $('#post').click(function(){
                $(this).addClass('add-class')
                $('#wiki').removeClass('add-class')
            })
            $('#wiki').click(function(){
                $(this).addClass('add-class')
                $('#post').removeClass('add-class')
            })
        })
    </script>

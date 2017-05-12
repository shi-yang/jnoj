</div><!--/row-->
</div><!--/.container-->
<footer class="footer">
    <div class="container">
        <p class="text-muted">&copy; 2017</p>
    </div>
</footer>
<?php if (!$current_user->is_valid()): ?>
    <div id="logindialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>Login</h3>
                </div>
                <form action="ajax/login.php" method="post" id="login_form" class="ajform form-horizontal">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="username">Username: </label>
                            <div class="col-sm-9">
                                <input type='text' class="form-control" name='username' id='username'
                                       placeholder="Username"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="password">Password: </label>
                            <div class="col-sm-9">
                                <input type='password' class="form-control" name='password' id='password'
                                       placeholder="Password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="cksave">Cookie:</label>
                            <div class="col-sm-9">
                                <select name='cksave' class="form-control" id='cksave'>
                                    <option value='0' selected>Never</option>
                                    <option value='1'>One Day</option>
                                    <option value='7'>One Week</option>
                                    <option value='30'>One Month</option>
                                    <option value='365'>One Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="msgbox" style="display:none"></span>
                        <input name='login' class="btn btn-primary" type='submit' value='Login'/>
                        <a class='toregister btn' href="#">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="regdialog" class="modal fade">
        <div class="modal-dialog" role=documen">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>Register</h3>
                </div>
                <form method="post" action="ajax/register.php" id="reg_form" class="form-horizontal ajform">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rusername">Username: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="username" id="rusername"
                                       placeholder="Username"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rpassword">Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="password" id="rpassword"
                                       placeholder="Password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rrpassword">Repeat Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="repassword" id="rrpassword"
                                       placeholder="Repeat Password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rnickname">Nickname: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nickname" id="rnickname"
                                       placeholder="Nickname"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rschool">School: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="school" id="rschool"
                                       placeholder="School"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="remail">Email: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="email" id="remail" placeholder="Email"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="msgbox" style="display:none"></span>
                        <input class="btn btn-primary" type="submit" name="name" value="Submit"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div id="modifydialog" class="modal fade">
        <div class="modal-dialog" role=documen">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>Modify My Information</h3>
                </div>
                <form method="post" action="ajax/user_modify.php" id="modify_form" class="form-horizontal ajform">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rusername">Username: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="username" id="rusername"
                                       placeholder="Username" value="<?= $current_user->get_val("username") ?>"
                                       readonly/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="ropassword">Old Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="ol_password" id="ropassword"
                                       placeholder="Old Password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rpassword">New Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="password" id="rpassword"
                                       placeholder="New Password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rrpassword">Repeat Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="repassword" id="rrpassword"
                                       placeholder="Repeat Password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rnickname">Nickname: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nickname" id="rnickname"
                                       placeholder="Nickname" value="<?= $current_user->get_val("nickname") ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="rschool">School: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="school" id="rschool" placeholder="School"
                                       value="<?= $current_user->get_val("school") ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" for="remail">Email: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="email" id="remail" placeholder="Email"
                                       value="<?= $current_user->get_val("email") ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="msgbox" style="display:none"></span>
                        <input class="btn btn-primary" type="submit" name="name" value="Modify"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<div id="newsshowdialog" class="modal fade" style="display:none">
    <div class="modal-dialog" role=documen">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 id="ntitle">News Title</h3>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:10px">by <b id="snauthor"></b> <span id="sntime"></span></div>
                <div id="sncontent"></div>
            </div>
            <?php if ($current_user->is_root()): ?>
                <div class="modal-footer">
                    <a class="newseditbutton btn btn-primary" name="" href="">Edit</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="assets/js/end.js?<?= filemtime("assets/js/end.js") ?>"></script>
</body>
</html>

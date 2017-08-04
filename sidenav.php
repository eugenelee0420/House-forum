<nav>
  <div class="nav-wrapper purple lighten-2">
		<div class="row">
			<div class="col s12">
				<!-- Logo -->
    		<a href="index.php" class="brand-logo center flow-text hide-on-small-only">House Forums</a>
        <a href="index.php" class="brand-logo right flow-text hide-on-med-and-up">House Forums</a>

				<!-- Sidenav -->
				<ul id="slide-out" class="side-nav">

					<!-- User-view -->
					<li><div class="user-view">
						<div class="background">
							<img class="responsive-img" src="<?php echoGetUserSetting(getStudentId(session_id()),'bgPic'); ?>">
						</div>
						<?php echo '<a href="profile.php?studentId='.getStudentId(session_id()).'"><img class="circle" src="'.getUserSetting(getStudentId(session_id()),"avatarPic").'"></a>'; ?>
						<span class="white-text name"><?php echoGetUserName(session_id()); ?> (<?php echoGetUserGroupName(session_id()); ?>)</span>
						<span class="white-text email"><?php echoGetStudentId(session_id()); ?></span>
					</div></li>

					<!-- Menu -->
					<?php

					// Show house-specific forum link(s)

					// If the user only have permission to view one house-specific forum (the one they belong to)
					if (havePermission(session_id(),"VH") AND !havePermission(session_id(),"VAH")) {

            // Get user's hId
            $hId = getUserHId(session_id());

						// Find the fId of the user's house
						$stmt = $conn->prepare('SELECT fId, fName FROM forum WHERE hId = ?');
            $stmt->bind_param("s",$hId);
						$result = $stmt->execute();
						if (!$result) {
							die('Query failed. '.$stmt->error);
						}

						$stmt->bind_result($fId,$fName);
            $stmt->fetch();

						echo '<li><a href="viewforum.php?fId='.$fId.'" class="waves-effect"><i class="material-icons">chat</i>'.$fName.'</a></li>';

            $stmt->free_result();
            $stmt->close();

					} elseif (havePermission(session_id(),"VAH")) { // If user have permission to view all houses' forums

						// Find all house forums
						$sql = 'SELECT fId, fName FROM forum WHERE hId IS NOT NULL';
						$result = $conn->query($sql);
						if (!$result) {
							die('Query failed. '.$conn->error);
						}

						// List all the house forums
						while($row = mysqli_fetch_assoc($result)) {

							echo '<li><a href="viewforum.php?fId='.$row['fId'].'" class="waves-effect"><i class="material-icons">chat</i>'.$row['fName'].'</a></li>';

						}

            mysqli_free_result($result);

					}

					// Show inter-house forum link
					if (havePermission(session_id(),"VI")) {

						// Find the inter-house forum ID and name
						$sql = 'SELECT fId, fName FROM forum WHERE hId IS NULL LIMIT 1';
						$result = $conn->query($sql);
						if (!$result) {
							die('Query failed. '.$conn->error);
						}

						$row = mysqli_fetch_assoc($result);

						echo '<li><a href="viewforum.php?fId='.$row['fId'].'" class="waves-effect"><i class="material-icons">forum</i>'.$row['fName'].'</a></li>';

            mysqli_free_result($result);

          }

					// Divider
					if (havePermission(session_id(),"AGS") OR havePermission(session_id(),"AUS")) {
						echo '<li><div class="divider"></div></li>';
					}

					// Settings
					// Global settings
					if (havePermission(session_id(),"AGS")) {
						echo '<li><a href="settings_global.php"><i class="material-icons">settings</i>Global Settings</a></li>';
					}

					// userGroup settings
					if (havePermission(session_id(),"AUS")) {
						echo '<li><a href="settings_userGroup.php"><i class="material-icons">settings</i>User Group Settings</a></li>';
					}

					?>

					<li><div class="divider"></div></li>
					<li><a href="settings_user.php" class="waves-effect"><i class="material-icons">settings</i>User Settings</a></li>
					<li><a href="logout.php" class="waves-effect"><i class="material-icons">exit_to_app</i>Logout</a></li>
				</ul>
				<a href="#" data-activates="slide-out" class="button-collapse show-on-large"><i class="material-icons">menu</i></a>

			</div>
		</div>
  </div>
</nav>

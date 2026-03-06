<?php 
  session_start();
  include_once "db.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Realtime Chat App | Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }
        body{
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #0f0f0f;
            padding: 0 10px;
        }
        .wrapper{
            background: #1e1e1e;
            max-width: 450px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 0 128px 0 rgba(0,0,0,0.1),
                        0 32px 64px -48px rgba(0,0,0,0.5);
        }
        .users{
            padding: 25px 30px;
        }
        .users header,
        .users-list a{
            display: flex;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #333;
            justify-content: space-between;
        }
        .wrapper img{
            object-fit: cover;
            border-radius: 50%;
        }
        .users header img{
            height: 50px;
            width: 50px;
        }
        .users header .content{
            display: flex;
            align-items: center;
        }
        .users header .content .details{
            color: #fff;
            margin-left: 15px;
        }
        .users header .content .details span{
            font-size: 18px;
            font-weight: 500;
        }
        .users header .content .details p{
            font-size: 12px;
        }     
        .users header .logout{
            display: block;
            background: #333;
            color: #fff;
            outline: none;
            border: none;
            padding: 7px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 17px;
        }
        .users .search{
            margin: 20px 0;
            display: flex;
            position: relative;
            align-items: center;
            justify-content: space-between;
        }
        .users .search .text{
            font-size: 18px;
            color: #fff;
        }
        .users .search input{
            position: absolute;
            height: 42px;
            width: calc(100% - 50px);
            font-size: 16px;
            padding: 0 13px;
            border: 1px solid #333;
            outline: none;
            border-radius: 5px 0 0 5px;
            opacity: 0;
            pointer-events: none;
            transition: all 0.2s ease;
            color: #fff;
            background: #252525;
        }
        .users .search input.show{
            opacity: 1;
            pointer-events: auto;
        }
        .users .search button{
            position: relative;
            z-index: 1;
            width: 47px;
            height: 42px;
            font-size: 17px;
            cursor: pointer;
            border: none;
            background: #333;
            color: #fff;
            outline: none;
            border-radius: 0 5px 5px 0;
            transition: all 0.2s ease;
        }
        .users .search button.active{
            background: #00a884;
            color: #fff;
        }
        .users .search button.active i::before{
            content: '\f00d';
        }
        .users-list{
            max-height: 350px;
            overflow-y: auto;
        }
        :is(.users-list, .chat-box)::-webkit-scrollbar{
            width: 0px;
        }
        .users-list a{
            padding-bottom: 10px;
            margin-bottom: 15px;
            padding-right: 15px;
            border-bottom-color: #333;
        }
        .users-list a:last-child{
            margin-bottom: 0px;
            border-bottom: none;
        }
        .users-list a img{
            height: 40px;
            width: 40px;
        }
        .users-list a .content p{
            color: #bbb;
            font-size: 13px;
        }
        .users-list a .content span{
            color: #fff;
        }
        .users-list a .status-dot{
            font-size: 12px;
            color: #00a884;
            padding-left: 10px;
        }
        .users-list a .status-dot.offline{
            color: #555;
        }
    </style>
</head>
<body>
  <div class="wrapper">
    <section class="users">
      <header>
        <div class="content">
          <?php 
            $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
            if(mysqli_num_rows($sql) > 0){
              $row = mysqli_fetch_assoc($sql);
            }
          ?>
          <img src="images/<?php echo $row['img']; ?>" alt="">
          <div class="details">
            <span><?php echo $row['fname']. " " . $row['lname'] ?></span>
            <p><?php echo $row['status']; ?></p>
          </div>
        </div>
        <a href="php_logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a>
      </header>
      <div class="search">
        <span class="text">Select an user to start chat</span>
        <input type="text" placeholder="Enter name to search...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">
  
      </div>
    </section>
  </div>

  <script>
    const searchBar = document.querySelector(".search input"),
    searchIcon = document.querySelector(".search button"),
    usersList = document.querySelector(".users-list");

    searchIcon.onclick = ()=>{
      searchBar.classList.toggle("show");
      searchIcon.classList.toggle("active");
      searchBar.focus();
      if(searchBar.classList.contains("active")){
        searchBar.value = "";
        searchBar.classList.remove("active");
      }
    }

    searchBar.onkeyup = ()=>{
      let searchTerm = searchBar.value;
      if(searchTerm != ""){
        searchBar.classList.add("active");
      }else{
        searchBar.classList.remove("active");
      }
      let xhr = new XMLHttpRequest();
      xhr.open("POST", "php_search.php", true);
      xhr.onload = ()=>{
        if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
              let data = xhr.response;
              usersList.innerHTML = data;
            }
        }
      }
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.send("searchTerm=" + searchTerm);
    }

    setInterval(() =>{
      let xhr = new XMLHttpRequest();
      xhr.open("GET", "php_users.php", true);
      xhr.onload = ()=>{
        if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
              let data = xhr.response;
              if(!searchBar.classList.contains("active")){
                usersList.innerHTML = data;
              }
            }
        }
      }
      xhr.send();
    }, 500);
  </script>
</body>
</html>

create table user(
  id integer auto_increment,
  user_name varchar(20) not null,
  password varchar(255) not null,
  delete_flag tinyint(1) zerofill not null,
  updated_at datetime,
  created_at datetime,
  primary key(id),
  unique key user_name_index(user_name)
);

create table follow(
  id integer auto_increment,
  user_id integer,
  following_id integer,
  delete_flag tinyint(1) zerofill not null,
  updated_at datetime,
  created_at datetime,
  primary key(id, user_id, following_id)
);

create table tweet(
  id integer auto_increment,
  user_id integer not null,
  body varchar(255),
  updated_at datetime,
  created_at datetime,
  primary key(id),
  index user_id_index(user_id)
);

alter table follow add foreign key (user_id) references user(id);
alter table follow add foreign key (following_id) references user(id);
alter table tweet add foreign key (user_id) references user(id);



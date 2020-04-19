# INSTABOT (ARCHIVE)

#### English documentation will be write 

## Gekeli araçlar

- php 5.5+
- php-curl
- php pdo_mysql
- mysql database

## Kurulum

- database'i import et
- config.php'yi kendine gore ayarla
- database editorunu acip popular_accounts tablosuna likerlarini takip etmek istediginiz hesaplarin usernamelerini giriniz
- daha sonra `php cli.php initPopularAccounts` taskini calistirin bu task popular_accounts tablosundaki usernameId'leri dolduracaktir
- dependencyleri yukle `./composer install`  (vendor klasoru var ise dependencyler yuklenmis demektir.)

## Kullanim

Tanimli tasklar: 

- follow
- unfollow
- initPopularAccounts
- mediaCrawler

### initPopularAccounts

`popular_accounts` tablosundaki usernameId'si `0` olan kullanicilarin instagram useridlerini doldurur.

### mediaCrawler

bu task popular_accounts tablosundaki hesaplari gezerek son paylastiklari fotolarin likerlarini alip `followed` ve `unfollowed` flagleri `0` olarak `i_user` tablosuna yazar.


### follow

ornek: `php cli.php follow 30`

veritabanindan `followed` flag'i `0` olan kullanicilari cekerek verilen limit kadar follow istegi yollar. ornekte 30 takip istegi gonderilecektir.
follow istegi gonderdikten sonra `followed` flag'i 1 olacak ayrica `scheduled_unfollow_date` alanina o andan 2 gun sonrasina ayarlanacaktir.


### unfollow

ornek: `php cli.php follow 40`

veritabanindan `followed` flag'i `1`, `unfollowed` flag'i `0` ve `scheduled_unfollow_date` < NOW() olan kullanicilari cekerek verilen limit kadar unfollow istegi yollar. ornekte 40 takibi birak istegi gonderilecektir.


### syncFollowings

takip ettiğin kişiler ile db'yi sync eder


### Onemli Not

1 saat icinde 90 dan fazla follow unfollow request'i yapilmamalidir.


### ornek cronjob

```
0 * * * * /usr/bin/php /opt/instabot/cli.php follow 40 >> /var/log/instabot-follow.log
28 * * * * /usr/bin/php /opt/instabot/cli.php unfollow 30 >> /var/log/instabot-unfollow.log
0 13 * * * /usr/bin/php /opt/instabot/cli.php mediaCrawler >> /var/log/instabot-mediacrawler.log
0 0 * * * /usr/bin/php /opt/instabot/cli.php initPopularAccounts >> /var/log/instabot-initPopularAccounts.log
```
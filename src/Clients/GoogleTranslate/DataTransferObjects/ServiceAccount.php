<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\DataTransferObjects;

final readonly class ServiceAccount
{
    public function __construct(

        //public string $type,//": "service_account",
        //public string $projectId,//": "second-academy-427908-t0",
        public string $privateKeyId,//": "fcc08826c6fe7ba901a6d5a157f095534259299a",
        public string $privateKey,//": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDi0M6MlDOCwkH1\nGsWbVE/8/PlRjYPWA4HYUtixtvSTKbztOXDNwicRWmICETRPLwjRHD4XAASa9XY1\ntc6IKUW6d+tWzNkQsdCODNkTcw150TuARhHeR+Vop+Lk2ZyV9nIGPQg3TpdLUge9\nBVQVwOPb3TOiekt2JKr+EsQQAUODXd4WzSPJqAJkznc2ksw7/1SVRomfRDhPI37h\nyxV41OkGYyRLPgOtvze5ZT/yiHpAdPevRcXwAFSrGC3I0wgwqDd9gKAiZ8lOd0Jy\n61lzaWMjfr4xfzcl7G0U+BJ3qElLPRh+hvXFYE31Xv+p9PtpnG3U0xQ0v9C0FhBb\nYB6TPFY5AgMBAAECggEAUmA3xALHOoRG5DgW8LUMX/uYqS/WY9HeRKzCGrkx1ojg\nHuAvwerzuW+0DMAm2WNhtmClhEDzazwe9J7VJcqnknOfO2UmqNOLxSuRY2jzyfxd\nuda5ApvmC759v1PxdN2OyAk+hKe8dxSRzuqygTwPBXkvGaGE6qGioKg8IPv0gy7T\nBS6JSoS2b2FD5roQnJ2+7Z9UfaKmzpBckCsEvKVnwLd4j27XmNLNeQ2+M8BkJnom\n5jDeTR5VHc3i9MK8PfJZR8YOOcQnuxbKuY1IqQRkft/aO+j7qjbXcT6bh+1V0vVc\ntqNkyNp5XBaO3PZxvU9nWluSL/vzNvwoQejnHpr3KQKBgQD+C+5l9QG62cTVZQdg\nZmshWtfr07vuJbtunmN2AfV+YGVsmkikfzv3Q47QRDLjQFO68nd6M+EgWC9iJso5\nd0E27nHRjlCdqjw9VCV39H7XkVWSMSdzsLtmPocX/SnsFe1LU387TYv4bxCMowdg\no88BuOuSKuitVwOINCBeMmzIYwKBgQDkj0YYh8UYHF4n37h40coGaBNCQZfF0HJg\naCZT8YwZ2j/I9Ga4/ixz2eSOKs+ZoxIktM5ra58l6QA76Y+l3i3xPeedQZIstCSW\nE+jw7/GxKH788DJW27/iNC9RsvM8JJtJTpCbmPFJCtXZfPcgVfodVsp76aMiNqOs\nYHSMpmWzswKBgECJtWmPYX4fXoK7pLEXs7WIh3vwkTiBu2jxseDDxKLzSDDbzQKF\nFC3PqtM70BWtPNgsSq/vyAxYeskbg5ovspCK6L1MwywamC0YWGBt619GG5AFDrm0\nNxdVYSp19RV1yI+vSY4+OAXH6VNhAznIw4jzelzqq6uFhG8wltx3QO1zAoGBAI9t\nMUmXeaFlu9g91h9LVwGqMdu0Ga/y3LOO5+5pd8eJY9mRNR0Fs5OHuptUVi5NXMdY\nBuj2Akrh6lmueYxW3nGyrTPLwBT5frQHDniTuhG4HehQOuayw7kJkmAsceLd/eyE\nts5NraxudkAi4VmTWn8qxan4tXx02UUEyl6w1MVtAoGAVhSY+D6gCs9xcq2nCJ8f\nwWqNUjWPIo2I3VU53Qck4h6RbUgv0vsPYlE7ktPaHWqjlphamrEX1MhFLls9N9ze\n08SCPlA+TG+6XvymAFgXlGoEPVHz8ipjty7SFKuiFd1+P7Hx0EOgkRLuPS8a89OO\nOt91/QUjwg/PpxhyxO53iAc=\n-----END PRIVATE KEY-----\n",
        public string $clientEmail,//": "translate@second-academy-427908-t0.iam.gserviceaccount.com",
        //public string $clientId,//": "103913027980264117004",
        //public string $authUri,//": "https://accounts.google.com/o/oauth2/auth",
        public string $tokenUri,//": "https://oauth2.googleapis.com/token",
        //public string $authProviderX509CertUrl,//": "https://www.googleapis.com/oauth2/v1/certs",
        //public string $clientX509CertUrl,//": "https://www.googleapis.com/robot/v1/metadata/x509/translate%40second-academy-427908-t0.iam.gserviceaccount.com",
        //public string $universeDomain,//": "googleapis.com"
    ) {
    }
}
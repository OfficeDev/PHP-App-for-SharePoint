# SharePoint 用 PHP アプリのサンプル

[日本 (日本語)](/loc/README-ja.md) (日本語)

**目次**
- [概要](#overview)
- [前提条件](#prerequisites)
- [サンプルを構成する](#configure-the-sample)
- [サンプルを実行する](#run-the-sample)
- [プロジェクト ファイル](#project-files)

<a name="overview"></a>
## 概要

SharePoint のアプリ モデルを使用すると、特定の技術に結び付けずにアプリをビルドできます。お好みの言語とツールを使用して、アプリを構成するコンポーネントとページで作業することができます。 

このサンプルは、PHP を使用する SharePoint のアプリのビルドを開始するために作成されました。 

このサンプルは、[プロバイダーでホストされた](http://msdn.microsoft.com/library/office/fp179887.aspx#SelfHosted) SharePoint 用アプリです。このアプリは、PHP の [OAuth 2.0 クライアント ライブラリ](https://github.com/thephpleague/oauth2-client) を使用してアクセス トークンを取得します。PHP ページでは、トークンを使用して SharePoint の REST エンドポイントに認証済みの要求を発行します。 

<img alt="SharePoint 用 PHP アプリの概念図" src="../PHPAppSharePoint diagram.png">
図 1。SharePoint 用 PHP アプリの概念図

簡単にですが、お好みの技術である PHP を使用して、SharePoint 用アプリのビルドを開始するために知る必要がある基本概念を示しています。

<a name="prerequisites"></a>
## 前提条件

このサンプルを実行するには次のものが必要です。

- PHP バージョン 5.3 以上の Web サーバー。
- Office 365 の SharePoint サイト。試用版のサイトを取得するには、「[Office 365 Developer](https://portal.office.com/Signup/Signup.aspx?OfferId=6881A1CB-F4EB-4db3-9F18-388898DAF510&DL=DEVELOPERPACK&ali=1)」を参照してください。

<a name="configure-the-sample"></a>
## サンプルを構成する

サンプルを構成するには、次の作業を実行する必要があります。

1. [作成ツールを使用して依存関係を取得する](#get-the-dependencies)
2. [Web サーバーで Web アプリケーションを作成する](#create-a-web-application)
3. [SharePoint でアプリを登録する](#register-your-app-in-sharepoint)
4. [Config.php ファイルを更新する](#update-the-configuration-file)
5. [アプリのマニフェストで *client_id* を更新する](#update-the-app-manifest)
6. [アプリを SharePoint サイトに展開する](#deploy-the-app-to-the-sharepoint-site)

<a name="get-the-dependencies"></a>
### 依存関係を取得する

このサンプルでは、PHP の [OAuth 2.0 クライアント ライブラリ](https://github.com/thephpleague/oauth2-client)を使用しています。ライブラリを取得するには、[作成ツール](https://getcomposer.org/)を使用します。この手順に従って依存関係を取得します。

1. コマンド プロンプトで、このプロジェクトのクローンの作成またはダウンロードを実行するフォルダーに移動します。
2. **[PHP]** フォルダーに移動します。
3. 「`composer install`」と入力します。

作成ツールは、プロジェクトで必要となる依存関係をダウンロードします。

<a name="create-a-web-application"></a>
### Web アプリケーションを作成する。

サンプルは、**PHPAppWeb** を解決する Web アプリケーションを使用するように構成されています。アプリを構成しやすくするため、Web アプリケーションの名前として PHPAppWeb を使用することをお勧めします。次の手順に従って、Web アプリケーションを作成します。

1. PHPAppWeb という名前で Web サーバーに Web アプリケーションを作成します。
2. サンプルに含まれている **PHP** フォルダーへの Web アプリケーションの物理パスをマップします。

**注**:Web アプリケーションは、SSL を使用するように構成する必要があります。

<a name="register-your-app-in-sharepoint"></a>
### SharePoint にアプリを登録する

アプリは [Office ストア](http://store.office.com)からインストールしているのではないので、アプリを展開する前に、SharePoint に登録する必要があります。アプリケーションを登録するには、次の手順に従います。

1. ブラウザーを開き、https://*yoursharepointsite*/_layouts/15/appregnew.aspx に移動します。
2. ページの次のフィールドに記入します。
	- クライアント ID - [生成] ボタンをクリックすると、SharePoint が値を生成します。
	- クライアント シークレット - [生成] ボタンをクリックすると、SharePoint が値を生成します。
	- タイトル - PHPAppforSharePoint
	- アプリのドメイン - localhost
	- リダイレクト URI - https://localhost/phpappweb/index.php
3. 値をコピーし、将来の参照用に保存します。この値は、構成およびアプリのマニフェスト ファイルの更新時に必要になります。
4. [作成] をクリックします。

> 注:PHPAppforSharePoint 以外のタイトルを指定した場合は、状況に応じてアプリ マニフェストの App 要素の Name 属性を更新する必要があります。

<a name="update-the-configuration-file"></a>
### 構成ファイルを更新する

サンプルには、前の作業のアプリの登録ページにある client_id と client_secret の値が必要になります。config.php ファイルの値を更新するには、次の手順を実行します。

1. テキスト エディターで、このサンプルにある **config.php** ファイルを開きます。
2. 前の作業で取得した client_id と client_secret の値を、ファイルのプレースホルダーにコピーします。

	```
    $client_id = "<your client_id value>";
	$client_secret = "<your client_secret value>";
	```
3. config.php ファイルを保存します。

<a name="update-the-app-manifest"></a>
### アプリ マニフェストを更新します。

AppTemplate.app ファイルは、アプリのマニフェスト ファイルを含むアプリのパッケージです。**SharePoint にアプリを登録する**作業で取得した client_id 値でアプリのマニフェスト ファイルを更新する必要があります。この手順に従って、アプリのマニフェスト ファイルを更新します。

1. サンプルの root フォルダーに移動します。
2. このサンプルで提供されている **AppPackage.app** ファイルの名前を **AppPackage.zip** に変更します。
3. zip ファイルを開き、**AppManifest.xml** ファイルを抽出します。
4. AppManfest.xml ファイルを編集します。*<client_id の値>*を、[アプリの登録] ページのクライアント ID の値に置き換えます。

	```
	<AppPrincipal>
        <RemoteWebApplication ClientId="<your client_id value>"/>
    </AppPrincipal>
	```
4. zip ファイル内の AppManifest.xml ファイルを置き換えます。
5. AppPackage.zip ファイルの名前を AppPackage.app に戻します。

**注**:更新された AppPackage.app ファイルは、元のファイルと同じファイル構造を維持する必要があります。ファイルまたはフォルダーを移動すると、パッケージが無効になります。

<a name="deploy-the-app-to-the-sharepoint-site"></a>
### アプリを SharePoint サイトに展開する

管理者がテナント内の SharePoint サイトにビジネス アプリを展開できるようにするアプリ カタログを使用してアプリを展開することができます。

1. テナントにアプリ カタログ サイトがない場合は、それを作成します。詳細については、「[アプリ カタログ サイトを作成する](http://office.microsoft.com/ja-jp/sharepoint-help/use-the-app-catalog-to-make-custom-business-apps-available-for-your-sharepoint-online-environment-HA102772362.aspx#_Toc347303048)」を参照してください。
2. アプリ カタログに AppTemplate.app ファイルを追加します。詳細については、「[アプリ カタログ サイトにカスタム アプリを追加する](http://office.microsoft.com/ja-jp/sharepoint-help/use-the-app-catalog-to-make-custom-business-apps-available-for-your-sharepoint-online-environment-HA102772362.aspx#_Toc347303049)」を参照してください。
3. アプリを展開する SharePoint サイトに移動します。[設定] メニュー (ページの右上隅にある歯車) から、**[アプリの追加]** を選択します。
4. [SharePoint 用 PHP アプリ] を選択します。[同意] ページで、**[信頼する]** を選択します。

<a name="run-the-sample"></a>
## サンプルを実行する

サンプルを実行するには、[SharePoint サイトのコンテンツ] ページで、**[SharePoint 用 PHP アプリ]** をクリックします。コンテンツのページを見つけるには、サイトに移動して、[設定] メニュー (ページの右上隅にある歯車) をクリックし、**[サイトのコンテンツ]** を選択します。

<a name="project-files"></a>
## プロジェクト ファイル

サンプルには、ご使用の環境でアプリの構成とテストを実行する次のコンポーネントが含まれています。  

### SharePoint.php
これは、**AbstractProvider** クラスから継承した PHP クラスです。AbstractProvider クラスは、承認された要求を発行するために使用するトークンを取得する関数を公開します。
 
このクラスは、アクセス トークンを取得するために必要な値を初期化します。コンストラクターには、パラメーターとしてクライアント ID、クライアント シークレット、SharePoint サイトの URL、およびコンテキスト トークンが必要です。コンストラクターは、コンテキスト トークンの次の値を抽出し、フォーマットします。

- refreshtoken
- トークンのサービス URI
- リソース

SharePoint プロバイダー オブジェクトを作成すると、PHP ページで、トークンを取得してから HTTP 要求で使用する AbstractProvider クラスの **getAccessToken** メソッドを呼び出すことができます。

### ページの例
**Index.php** は、SharePoint プロバイダー クラスの使用方法を示す例です。このページでは、以下を実行します。

- 要求が POST メソッドを使用していることを確認します。要求が GET を使用している場合は、SharePoint サイトのコンテンツ ページからアプリを起動するようユーザーに伝えます。
- SharePoint プロバイダーのインスタンスを初期化します。
- AbstractProvider クラスの getAccessToken 関数を使用してアクセス トークンを取得します。
- SharePoint サイトで公開された REST エンドポイントに認証済みの要求を発行します。
- ブラウザーに結果を出力します。

### 構成ファイル
index.php ページで、**config.php** ファイルから次の構成値を読み取ります。

- client_id
- client_secret

これらの値は、SharePoint の [アプリの登録] ページから取得できます。詳細については、「[構成ファイルを更新する](#update-the-configuration-file)」を参照してください。 

### アプリ パッケージの例
AppPackage.app ファイルは、アプリの展開とテストに使用できる、SharePoint パッケージ用のアプリです。

## 著作権

Copyright (c) Microsoft. All rights reserved.



# PHP App for SharePoint sample

[日本 (日本語)](/loc/README-ja.md)

**Table of Contents**
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Configure the sample](#configure-the-sample)
- [Run the sample](#run-the-sample)
- [Project Files](#project-files)

<a name="overview"></a>
## Overview

The app model in SharePoint lets you build apps without tying you to a particular technology. You can use the language and tools of your preference to work on the components and pages that constitute your app. 

We built this sample to get you started in building an app for SharePoint that uses PHP. 

This sample is a [provider-hosted](http://msdn.microsoft.com/library/office/fp179887.aspx#SelfHosted) app for SharePoint that gets an access token by using the [OAuth 2.0 client library](https://github.com/thephpleague/oauth2-client) for PHP. A PHP page uses the token to issue an authenticated request to a REST endpoint in SharePoint. 

<img alt="PHP App for SharePoint conceptual diagram" src="PHPAppSharePoint diagram.png">
Figure 1. PHP App for SharePoint conceptual diagram

Simple, but it illustrates the basic concepts that you need to know to start building apps for SharePoint using your favorite technology, PHP.

<a name="prerequisites"></a>
## Prerequisites

This sample requires the following:

- Web server with PHP version 5.3 or higher.
- SharePoint site on Office 365. To get a trial site, see [Sign up for an Office 365 Developer Subscription](https://portal.office.com/Signup/Signup.aspx?OfferId=6881A1CB-F4EB-4db3-9F18-388898DAF510&DL=DEVELOPERPACK&ali=1).

<a name="configure-the-sample"></a>
## Configure the sample

You must complete the following tasks to configure the sample:

1. [Get the dependencies using composer](#get-the-dependencies)
2. [Create a web application in your web server](#create-a-web-application)
3. [Register your app in SharePoint](#register-your-app-in-sharepoint)
4. [Update the config.php file](#update-the-configuration-file)
5. [Update the *client_id* in the app manifest](#update-the-app-manifest)
6. [Deploy the app to the SharePoint site](#deploy-the-app-to-the-sharepoint-site)

<a name="get-the-dependencies"></a>
### Get the dependencies

The sample uses the [OAuth 2.0 client library](https://github.com/thephpleague/oauth2-client) for PHP. You can use [Composer](https://getcomposer.org/) to get the library. Follow this procedure to get the dependencies:

1. In a command prompt, go to the folder where you cloned or downloaded this project.
2. Go to the **PHP** folder.
3. Type `composer install`

Composer downloads the dependencies required by the project.

<a name="create-a-web-application"></a>
### Create a web application

The sample is configured to use a web application that resolves to **PHPAppWeb**. To make it easier to configure your app we recommend that you use PHPAppWeb as your web application name. Follow this procedure to create the web application:

1. Create a web application on your web server with a name of PHPAppWeb.
2. Map the web application physical path to the **PHP** folder included in the sample.

**Note**: The web application must be configured to use SSL.

<a name="register-your-app-in-sharepoint"></a>
### Register your app in SharePoint

Since you're not installing the app from the [Office Store](http://store.office.com), you must register your app in SharePoint before deploying it. Follow this procedure to register the app:

1. Open a browser and go to https://*yoursharepointsite*/_layouts/15/appregnew.aspx
2. Fill the following fields in the page:
	- Client Id - Click the Generate button to let SharePoint create a value for you.
	- Client Secret - Click the Generate button to let SharePoint create a value for you.
	- Title - PHPAppforSharePoint
	- App Domain - localhost
	- Redirect URI - https://localhost/phpappweb/index.php
3. Copy the values and store for future reference. You'll need them to update the configuration and app manifest files.
4. Click Create.

> Note: If you specify a title other than PHPAppforSharePoint you must update the Name attribute of the App element in the app manifest accordingly.

<a name="update-the-configuration-file"></a>
### Update the configuration file

The sample requires the client_id, and client_secret values from the app registration page in the previous task. To update the values in the config.php file, follow this procedure:

1. Open the **config.php** file provided in this sample in a text editor.
2. Copy the client_id, and client_secret values that you got in the previous task to the placeholders in the file.

	```
    $client_id = "<your client_id value>";
	$client_secret = "<your client_secret value>";
	```
3. Save the config.php file.

<a name="update-the-app-manifest"></a>
### Update the app manifest

The AppTemplate.app file is an app package that contains the app manifest file. You have to update the app manifest file with the client_id value obtained in the **Register your app in SharePoint** task. Follow this procedure to update the app manifest file:

1. Go to the root folder of the sample.
2. Rename the **AppPackage.app** file provided in this sample to **AppPackage.zip**
3. Open the zip file and extract the **AppManifest.xml** file.
4. Edit the AppManfest. xml file. Replace *<your client_id value>* with the Client Id value from the app registration page.

	```
	<AppPrincipal>
        <RemoteWebApplication ClientId="<your client_id value>"/>
    </AppPrincipal>
	```
4. Replace the AppManifest.xml file in the zip file.
5. Rename the AppPackage.zip file back to AppPackage.app

**Note**: The updated AppPackage.app file must keep the same file structure as the original. Moving files or folders can invalidate your package.

<a name="deploy-the-app-to-the-sharepoint-site"></a>
### Deploy the app to the SharePoint site

You can deploy the app using the app catalog that lets administrators deploy business apps to SharePoint sites in the tenant.

1. If you don't have an app catalog site in your tenant, create one. For more information, see [Create an App Catalog site](http://office.microsoft.com/en-us/sharepoint-help/use-the-app-catalog-to-make-custom-business-apps-available-for-your-sharepoint-online-environment-HA102772362.aspx#_Toc347303048).
2. Add the AppTemplate.app file to the app catalog. For more information, see [Add custom apps to the App Catalog site](http://office.microsoft.com/en-us/sharepoint-help/use-the-app-catalog-to-make-custom-business-apps-available-for-your-sharepoint-online-environment-HA102772362.aspx#_Toc347303049).
3. Go to the SharePoint site where you want to deploy the app. From the Settings menu (the gear in the top right corner of the page) choose **Add an app**.
4. Choose the PHP App for SharePoint app. In the consent page, choose **Trust It**.

<a name="run-the-sample"></a>
## Run the sample

To run the sample, simply click on the **PHP App for SharePoint** app in the SharePoint site contents page. To find the contents page go to your site, click on the Settings menu (the gear in the top right corner of the page), and choose **Site contents**.

<a name="project-files"></a>
## Project Files

The sample includes the following components that help you configure and test the app in your environment.  

### SharePoint.php
This is a PHP class that inherits from the **AbstractProvider** class. The AbstractProvider class exposes a function to get a token that you can use to issue authorized requests.
 
The class initializes the values required to get the access token. The constructir requires the client id, client secret, SharePoint site URL, and a context token as parameters. The constructor extracts and formats the following values from the context token:

- Refresh token
- Token service URI
- Resource

After creating a SharePoint provider object, PHP pages can call the **getAccessToken** method of the AbstractProvider class to get a token and use it in HTTP requests.

### Example page
**Index.php** is an example that shows you how to use the SharePoint provider class. The page does the following:

- Checks that the request is using the POST method. If the request is using GET instead it tells the user to start the app from the SharePoint site contents page.
- Initializes a SharePoint provider instance.
- Gets an access token using the getAccessToken function of the AbstractProvider class.
- Issues an authenticated request to a REST endpoint exposed by the SharePoint site.
- Prints the result to the browser.

### Configuration file
The index.php page reads the following configuration values from the **config.php** file:

- client_id
- client_secret

You can get these values from the app registration page in SharePoint. For more information, see [Update the configuration file](#update-the-configuration-file). 

### Example app package
The AppPackage.app file is an app for SharePoint package that you can use to deploy and test your app.

## Copyright

Copyright (c) Microsoft. All rights reserved.

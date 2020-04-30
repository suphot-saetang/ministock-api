<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
// use Tuupola\Middleware\CorsMiddleware;

return function (App $app)
{

    $container = $app->getContainer();

    // $app->add(new Tuupola\Middleware\CorsMiddleware);

    $app->get('/', function (Request $request, Response $response, array $args) use ($container)
    {
        echo "<h1 style='margin-top:50vh; text-align:center; font-size:60px;'>Stock API</h1>";
    });

    //==================================================================
    // CRUD TABLE Products
    //=================================================================
    // ดึงข้อมูลจากตาราง products ออกมาแสดงเป็น json
    $app->group('/api', function () use ($app)
    {

        $container = $app->getContainer();

        $app->get('/', function (Request $request, Response $response, array $args) use ($container)
        {
            echo "<h1 style='margin-top:50vh; text-align:center; font-size:60px;'>Stock API</h1>";
        });

        // Test Method get
        $app->get('/testget', function (Request $request, Response $response, array $args) use ($container)
        {
            echo "GET Method Success";
        });

        // Test Method get
        $app->post('/testpost', function (Request $request, Response $response, array $args) use ($container)
        {
            $body = $this->request->getParsedBody();
            print_r($body);
        });

        // List product
        $app->get('/products', function (Request $request, Response $response, array $args) use ($container)
        {
            $sth = $this->db->prepare("SELECT * FROM products ORDER BY id DESC");
            $sth->execute();
            $product = $sth->fetchAll();
            // แสดงผลออกมาเป็น JSON

            return $this->response->withJson($product);
        });

        // List product by procedure
        $app->get('/products/procedure', function (Request $request, Response $response, array $args)
        {

            $barcode = "98390389304894";
            $sql     = 'CALL getProductByCode(:code)';
            $sth     = $this->db->prepare($sql);
            $sth->bindParam(":code", $barcode);
            $sth->execute();
            $product = $sth->fetchAll();

            // แสดงผลออกมาเป็น JSON

            return $this->response->withJson($product);
        });

        // Add product
        $app->post('/products', function (Request $request, Response $response, array $args) use ($container)
        {
            $img  = "noimg.jpg";
            $body = $this->request->getParsedBody();
            $sql  = "INSERT INTO products(product_name,product_detail,product_barcode,product_price,product_qty,product_image)
                        VALUES(:product_name,:product_detail,:product_barcode,:product_price,:product_qty,:product_image)";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("product_name", $body['product_name']);
            $sth->bindParam("product_detail", $body['product_detail']);
            $sth->bindParam("product_barcode", $body['product_barcode']);
            $sth->bindParam("product_price", $body['product_price']);
            $sth->bindParam("product_qty", $body['product_qty']);
            $sth->bindParam("product_image", $img);

            if ($sth->execute())
            {
                $data  = $this->db->lastInsertId();
                $input = [
                    'id'     => $data,
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => '',
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // Get Products By ID (ดึงรายชื่อสินค้าระบุ id)
        $app->get('/product/{id}', function (Request $request, Response $response, array $args)
        {
            $sql = $this->db->prepare("SELECT * FROM products WHERE id='$args[id]'");
            $sql->execute();
            $result = $sql->fetchAll();
            // print_r($result);
            // แปลง array เป็น JSON

            return $this->response->withJson($result);
        });

        // Test PUT
        $app->put('/updatedata', function ()
        {
            echo "PUT OK";
        });

        // Test Delete
        $app->delete('/deletedata', function ()
        {
            echo "Delete OK";
        });

        // Edit product
        $app->put('/product/{id}', function (Request $request, Response $response, array $args)
        {
            $body = $this->request->getParsedBody();
            $sql  = "UPDATE  products SET
                            product_name=:product_name,
                            product_detail=:product_detail,
                            product_barcode=:product_barcode,
                            product_price=:product_price,
                            product_qty=:product_qty
                    WHERE id='$args[id]'";

            $sth = $this->db->prepare($sql);
            $sth->bindParam("product_name", $body['product_name']);
            $sth->bindParam("product_detail", $body['product_detail']);
            $sth->bindParam("product_barcode", $body['product_barcode']);
            $sth->bindParam("product_price", $body['product_price']);
            $sth->bindParam("product_qty", $body['product_qty']);

            if ($sth->execute())
            {
                $data  = $args['id'];
                $input = [
                    'id'     => $data,
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => '',
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // Delete product
        $app->delete('/product/{id}', function (Request $request, Response $response, array $args)
        {
            $body = $this->request->getParsedBody();
            $sql  = "DELETE FROM products WHERE id='$args[id]'";

            $sth = $this->db->prepare($sql);

            if ($sth->execute())
            {
                $data  = $args['id'];
                $input = [
                    'id'     => $data,
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => '',
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // User login
        $app->post('/user/login', function (Request $request, Response $response, array $args)
        {

            // รับค่า username และ password จากผู้ใช้
            $body = $this->request->getParsedBody();

            $username = $body['username'];
            $password = sha1($body['password']);

            $sql = $this->db->prepare("SELECT id,username,password,fullname,img_profile FROM users
                                        WHERE username=:username and password=:password");

            $sql->bindParam("username", $username);
            $sql->bindParam("password", $password);
            $sql->execute();

            // นับจำนวนแถวที่พบ
            $count = $sql->rowCount();
            //echo $count;

            // อ่านข้อมูลออกมาแสดง
            $result = $sql->fetchAll();

            // print_r($result);
            if ($count >= 1)
            {
                $input = [
                    'userid'      => $result[0]['id'],
                    'fullname'    => $result[0]['fullname'],
                    'img_profile' => $result[0]['img_profile'],
                    'status'      => 'success',
                ];
            }
            else
            {
                $input = [
                    'userid'      => '',
                    'fullname'    => '',
                    'img_profile' => '',
                    'status'      => 'fail',
                ];
            }

            return $this->response->withJson($input);

        });

    });

};

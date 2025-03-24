# CloudCart Product Importer

This project is a Laravel-based product importer for CloudCart, allowing bulk product uploads via CSV.

## Setup Instructions

### 1. Clone the Repository
First, clone this repository from GitHub:

```sh
git clone https://github.com/bugratepecik/cloudcart-importer.git
cd cloudcart-importer
```

### 2. Start Docker
Ensure that Docker is installed and running. Then, start the containers:

```sh
docker-compose up -d --build
```

### 3. Enter the Application Container
Once the containers are running, access the application container:

```sh
docker exec -it cloudcart_app fish
```

### 4. Install Dependencies
Inside the container, run:

```sh
composer install
npm install
```

### 5. Run Migrations
To create the necessary tables, run:

```sh
php artisan migrate
```

### 6. Create a Docker Network (If Required)
Run the following command to ensure network connectivity:

```sh
docker network create cloudcart_network
```

### 7. Start the Application
To start the Laravel server, run:

```sh
php artisan serve
```

The application will be available at:  
👉 `http://localhost:8000/cloudcart/upload`

## API Token Configuration
To communicate with the CloudCart API, ensure you have the correct API key and bearer token.  
These should be set in the `.env` file:

```env
CLOUDCART_API_KEY=your_api_key
CLOUDCART_BEARER_TOKEN=your_bearer_token
```

## Running & Testing the Import
### 1. Access the Frontend
Go to:  
👉 `http://localhost:8000/cloudcart/upload`

From this page, you can upload a CSV file using the provided template.

### 2. Monitoring Logs and Database
To inspect log records and the product database:

- Open **pgAdmin** (`http://localhost:5050/`)
- Use the following credentials:
    - **Email:** `admin@admin.com`
    - **Password:** `admin`
- Check the `products` table to verify the imported data.

### 3. Example CSV Format
Make sure your CSV file follows this format. You can find the example CSV file inside the `storage` directory:

```csv
name,sku,price,quantity,brand,category,tags,description,image_url,variant_1_name,variant_1_value,variant_2_name,variant_2_value
Sample Product,12345,99.99,10,BrandX,Apparel,tag1;tag2,Sample Description,https://example.com/image.jpg,Size,M,Color,Red
```

## Notes
- Ensure that your `.env` file is correctly set up before running the application.
- If you encounter permission issues, try running `chmod -R 777 storage bootstrap/cache` inside the container.
- If you need to restart the containers, use:

  ```sh
  docker-compose down && docker-compose up -d --build
  ```



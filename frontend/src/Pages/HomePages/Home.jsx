import { Link } from "react-router-dom";
import heroImage from "../../assets/logistic_best.webp";

const Home = () => {
  return (
    <section className="flex items-center justify-center min-h-screen overflow-hidden">
      <div className="container px-4 py-5 mx-auto">
        <div className="flex flex-col md:flex-row gap-8 md:gap-12 lg:gap-16">
          <div className="w-full md:w-1/2 order-2 md:order-1 my-auto">
            <h1 className="font-bold text-3xl sm:text-4xl md:text-5xl lg:text-6xl leading-tight">
              ShipSync Streamline Your
              <br className="hidden md:block" />
              Shipping Operations
            </h1>

            <p className="text-gray-600 mt-4 text-base sm:text-lg md:text-xl">
              Effortlessly manage your logistics and track shipments in
              real-time with ShipSync, your ultimate shipping dashboard.
            </p>

            <div className="mt-6 md:mt-8">
              <Link
                to="/register"
                className="inline-block px-6 py-2.5 md:px-7 md:py-3 bg-gray-900 text-white rounded-full hover:bg-gray-800 transition-colors font-medium text-sm md:text-base"
              >
                Get Started
              </Link>
            </div>
          </div>

          <div className="w-full md:w-1/2 order-1 md:order-2 my-auto">
            <img src={heroImage} alt="Logistics" className="w-full h-auto" />
          </div>
        </div>
      </div>
    </section>
  );
};

export default Home;
